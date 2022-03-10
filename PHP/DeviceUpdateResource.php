<?php
/***
Copyright Train Control Systems Inc. 2020
Document: WOWPOWIndicator.vue
Purpose: This file generates a loading bar that sends out messages at completion
        and receives statuses in percentages (0.0 - 1.0) which update how full
        the loading bar is.
Developer: JMM
Date: August 15th 2021
***/

namespace Drupal\tcs_depot\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a Device Patch Resource for Throttle
 * @RestResource(
 *  id = "tcs_depot_patch_resource",
 *  label = @Translation("TCS Depot Patch Resource"),
 *  uri_paths = {
 *      "canonical" = "/tcs_depot/DeviceUpdateResource"
 *  }
 *)
 */


class DeviceUpdateResource extends ResourceBase{

/**
 * Responds to entity GET requests.
 * @return \Drupal\rest\ResourceResponse
 */
    public function get(Request $request){
        $build = array(
            '#cache' => array(
                'max-age' => 0,
            ),
        );
        $product_ids = $request->query->get('product_ids');
        $product_ids = (strlen($product_ids) > 0 ? explode(",", $product_ids) : []);
        $patch_ids = $request->query->get('patch_ids');
        $returnArray = [];

        if(strlen($patch_ids)>0){
            $patch_ids = explode(',', $patch_ids);
            $returnArray["patch_files"] = $this->groupReturnPatchFiles($patch_ids);
            return (new ResourceResponse($returnArray))->addCacheableDependency($build);
        }

        $returnArray["patch_headers"] = $this->retrieveAllUpdates($product_ids);

        return (new ResourceResponse($returnArray))->addCacheableDependency($build);
    }
    
    //This function groups the file contents corresponding to the patch_ids received
    //and returns an array containing the file contents
    //@param patch_ids - array of patch file id's
    private function groupReturnPatchFiles($patch_ids){
        $returnArray = [];
        foreach($patch_ids as $key=>$value){
            $returnArray[$value] = $this->retrieveUpdateFile($value);
        }
        return $returnArray;
    }

    //This function retrieves a list of all nodes in content type "update" (this content type will probably change).
    //@param product_ids - array of product id's for the type of product the user is updating
    private function retrieveAllUpdates($product_ids=false){
        //$boolSpecificProducts = (count($product_ids) > 0 ? true : false);
        //Instantiate an array to contain the return value
        $returnArray = [];
        //Build a Drupal node query that retrieves all node id's of nodes with a type of 'update' (or whatver the final content type)
        //ends up being
        $entities = \Drupal::entityTypeManager()
          ->getListBuilder('software_file')
          ->getStorage()
          ->loadByProperties([
              'status' => 1,
          ]);
        //Loop through each entity id storing the id in "value"
        foreach($entities as $key=>$value){
            //Check if
            if(!$product_ids){
                $returnArray[] = $this->parseSingleUpdateNode($value->toArray());
            }
            else{
                if(in_array($value->getID(), $product_ids)){
                    $returnArray[] = $this->parseSingleUpdateNode($value->toArray());
                }
            }
        }
        return $returnArray;
    }
    
    //Retrieves the details of a patch from the deeply nested
    //data model, generate current hashes of the file, and return the complete array of file details.
    //@param patchNode - dictionary of patch details
    private function parseSingleUpdateNode($patchNode){
        //Array of node fields we need to retrieve
        $valuedKeys  = ['field_major_version', 'field_minor_version', 'field_subminor_version', 'field_update_description', 'field_update_file', 'title', 'status', 'field_date_created', 'status', 'revision_timestamp', 'field_product_id'];
        //Create the return output array
        $outputPatchArray = [];
        //Loop through each field in the node found above and store the field name in key and the field contents in value
        foreach($patchNode as $key=>$value){
            //Make sure the field key is in our list of requested keys
            if(in_array($key, $valuedKeys)){
                //Is the key for the compatible products field - products field is the only variable of type array we want to return
                //so it receives special treatment here.
                if($key == 'field_product_id'){
                    //instantiate the currentField as an array
                    $currentField = [];
                    //Loop through each product in the compatible products array
                    foreach($value as $compatibleProductIndex=>$compatibleProduct){
                        //and strip off the unnecessary array nesting and append the product id to the currentField
                        $currentField[] = $this->stripArray($compatibleProduct);
                    }
                }
                //Check to see if we are at the file id
                else if($key == 'field_update_file'){
                    $file_id = $this->stripArray($value);
                    //if we are, pull the file id from the array it is stored in, and load the file object into the variable file
                    $file = \Drupal\file\Entity\File::load($file_id);
                    //retrieve the hash of the loaded file from the drupal data model and insert it into our return array.
                    $outputPatchArray["file_hash_sha256"] = $file->filehash['sha256'];
                    $outputPatchArray["file_hash_md5"] = $file->filehash['md5'];
                    $currentField = $file_id;
                }
                else{
                    //Strip unnnecessary array nesting from current value and store it in currentField
                    $currentField = $this->stripArray($value);
                }
                //Retain the original field name and use it as the index in the output array for the currentField value
                $outputPatchArray[$key] = $currentField;
            }
        }
        return $outputPatchArray;
    }

    //Removes array nested data from deep nesting
    //@param currentField - Array of Arrays
    private function stripArray($currentField){
        //Loop while the currentField is still an array
        while(is_array($currentField)){
            //check if the currentField contains a value field
            if(isset($currentField['value'])){
                //set the currentField to the value found in the value index of the currentField
                $currentField = $currentField['value'];
            }
                //check if the currentfield contains a target_id field (used on fields that are references)
            else if(isset($currentField['target_id'])){
                //set the currentField to the value found in the target_id index of the currentField
                $currentField = $currentField['target_id'];
            }
            else{
                //set currentField to the last value in the currentField
                $currentField = end($currentField);
            }
        }
        //return the currentField value (now a simple value)
        return $currentField;
    }

    //Return an update file
    //@param fileID - Integer ID of the file being requested.
    private function retrieveUpdateFile($fileID){
        $file = \Drupal\file\Entity\File::load($fileID);
        return file_get_contents($file->getFileUri());
        //$file->filehash['sha1']
    }
}