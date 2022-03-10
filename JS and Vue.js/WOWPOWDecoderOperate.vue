/***
Copyright Train Control Systems Inc. 2019
Document: WOWPOWOperate.vue
Purpose: This file provides all of the UI and user feedback for sending images
         and commands to the WOWPOW for updating the WOWPOW and decoders.
Developer: Jordan M McBride
Date: April 5th 2021
***/
<template>
  <div class="mainPageContainer">
      <div class="activePane">
          <div class="mainPageHeader">
          WOWPOW Decoder Update
          </div>
          <div class="programSDSteps">
              <div class="programSDSingleStep">
                  <div class="stepText">
                      Select a file to send to the decoder:
                  </div>
                  <div class="stepForm">
                      <div class="updateFileSelectName" id="fileDisplay">
                           {{selectedFileName}}
                      </div>
                      <div class="fileUpdateFileContainer">
                          Select File...
                          <input type="file" id="updateFileSelect" name="updateFileSelect" @change="onFileChange"/>
                      </div>
                  </div>
              </div>
              <div class="programSDSingleStep" v-if="AdvanceChecked">
                  <div class="stepText">
                      Send a Text Command
                  </div>
                  <div class="stepForm">
                      <input v-model="textCommand">
                  </div>
              </div>
              <div class="programSDSingleStep" v-if="AdvanceChecked">
                  <div class="stepText">
                      Select COM Port:
                  </div>
                  <div class="stepForm">
                      <select id="COMDropDown" name="COMDropDown" v-on:change="selectPort" class="driveSelect" v-if="COMlistExists">
                          <option v-for="entry in WOWPOWInterface.portList" :value="entry" :key="entry">{{entry}}</option>
                      </select>
                      <div class="driveRefresh" @click="refreshPortList">
                          Refresh COM list
                      </div>
                  </div>
              </div>
              <div class="programSDSingleStep" v-if="!AdvanceChecked">
                  <div class="stepText">
                      WOWPOW Connection:
                  </div>
                  <div class="stepForm easyIndicator">
                     <WOWPOWIndicator/>
                  </div>
              </div>
              <div class="programSDSingleStep">
                  <div class="stepText">
                      WOWPOW Feedback:
                      <div class="feedbackBox" id="feedback_box">
                          <div v-for="entry in WOWPOWInterface.feedback" class="feedbackLine">{{entry}}</div>
                      </div>
                      <div class="feedbackControls">
                          <div class="feedbackLeftControls">

                          </div>
                          <div class="feedbackRightControls">
                              <SquareButton :buttonConfig="clearFeedback"/>
                          </div>
                      </div>
                  </div>
                  <div class="stepForm">
                    <div class="commandGrid">
                        <div class="bottomRowGrid">
                            <div v-if="notInWriteMode === 1" class="buttonFillIn">
                                <SquareButton :buttonConfig="sendNewImage"/>
                                <SquareButton :buttonConfig="sendNewFW"/>
                            </div>
                            <div v-else-if="notInWriteMode === 0" class="buttonFillIn">
                                <LoadingBar/>
                            </div>
                            <div v-else-if="notInWriteMode === 2" class="buttonFillIn">
                                <PulsingMessage/>
                            </div>
                            <div v-if="notInWriteMode && AdvanceChecked" class="buttonFillIn">
                                <SquareButton :buttonConfig="sendAnyCommand"/>
                            </div>
                        </div>

                        <div class="bottomRowGrid">
                            <div class="buttonFillIn">
                                <SquareButton :buttonConfig="stopSend"/>
                            </div>
                        </div>
                    </div>
                  </div>
              </div>
          </div>
      </div>
  </div>
</template>

<script>
import Tile from './../../components/UI/Tile.vue'
import CustomButton from './../../components/UI/Button.vue'
import SquareButton from './../../components/UI/SquareButton.vue'
import WOWPOWIndicator from './../../components/UI/WOWPOWIndicator.vue'
import LoadingBar from './../../components/UI/LoadingBar.vue'
import PulsingMessage from './../../components/UI/PulsingMessage.vue'

const { ipcRenderer } = require('electron')
/**
*
*@module pages/programming/WOWPOWOperate
*@description This component provides the entire interface for controlling the
* WOWPOW, sending images to the WOWPOW, and Updating devices.
*/
export default {
  name: 'WOWPOWDecoderOperate',
  components: {
    Tile,
    CustomButton,
    SquareButton,
    LoadingBar,
    PulsingMessage,
    WOWPOWIndicator
  },
  data: function () {
    return {
      AdvanceChecked: false,
      baudRate: 2000000,
      textCommand: '',
      notInWriteMode: 1,
      COMlistExists: false,
      selectedPort: '',
      badPorts: [],
      portApproved: false,
      WOWPOWInterface: {
        portList: ['None'],
        feedback: []
      },
      scrollToBottom: {
        foregroundColor: 'text_light_fore',
        backgroundColor: 'color_medium_back',
        text: 'Scroll to Bottom',
        buttonAction: function () {
          ipcRenderer.send('to_win',
            { 'function': 'scrollBottom',
              'params': {} })
        }
      },
      clearFeedback: {
        foregroundColor: 'text_light_fore',
        backgroundColor: 'color_medium_back',
        text: 'Clear',
        buttonAction: function () {
          ipcRenderer.send('to_win',
            { 'function': 'clearFeedback',
              'params': {} })
        }
      },
      sendNewImage: {
        foregroundColor: 'text_light_fore',
        backgroundColor: 'color_medium_back',
        text: 'Update Entire Decoder',
        buttonAction: function () {
          ipcRenderer.send('to_bgWin',
            { 'function': 'sendImage',
              'params': {} })
        }
      },
      sendNewFW: {
        foregroundColor: 'text_light_fore',
        backgroundColor: 'color_medium_back',
        text: 'Update Decoder Firmware',
        buttonAction: function () {
          ipcRenderer.send('to_bgWin',
            { 'function': 'sendFW',
              'params': {} })
        }
      },
      updateDecoder: {
        foregroundColor: 'text_light_fore',
        backgroundColor: 'color_medium_back',
        text: 'Update Decoder from WOWPOW SD Card',
        buttonAction: function () {
          ipcRenderer.send('to_bgWin',
            { 'function': 'sendCommand',
              'params': { 'command': 'OTRE' } })
        }
      },
      updateWOWPOW: {
        foregroundColor: 'text_light_fore',
        backgroundColor: 'color_medium_back',
        text: 'Update WOWPOW from WOWPOW SD Card',
        buttonAction: function () {
          ipcRenderer.send('to_bgWin',
            { 'function': 'sendCommand',
              'params': { 'command': 'WOWPOW' } })
        }
      },
      sendAnyCommand: {
        foregroundColor: 'text_light_fore',
        backgroundColor: 'color_medium_back',
        text: 'SEND ANY COMMAND',
        buttonAction: function () {
          ipcRenderer.send('to_win',
            { 'function': 'sendText',
              'params': {} })
        }
      },
      stopSend: {
        foregroundColor: 'text_light_fore',
        backgroundColor: 'color_medium_back',
        text: 'Stop Send',
        buttonAction: function () {
          ipcRenderer.send('to_bgWin',
            { 'function': 'stopSend',
              'params': {} })
          ipcRenderer.send('to_win',
            { 'function': 'ExitWriteMode',
              'params': {} })
        }
      },
      pageState: {
        decoder: 'none',
        type: 'none'
      },
      /*
    * 0 = Not started
    * 1 = Current step
    * 2 = Completed step
    */
      stepState: [
        1,
        0,
        0
      ],
      step_1_class: 'stepIdentifier stepCurrent',
      step_2_class: 'stepIdentifier stepUpcoming',
      step_3_class: 'stepIdentifier stepUpcoming',
      stateClasses: [
        'stepIdentifier stepUpcoming',
        'stepIdentifier stepCurrent',
        'stepIdentifier stepDone'
      ],
      selectedFileName: ' ',
      fileStatus: 0,
      selectedFilePath: ''

    }
  },
  computed: {
    selectedAction: function () {
      // commenting this section out temporarily to make the UX simpler.
      return true
    }
  },
  watch: {
    WOWPOWInterface: {
      handler: function () {},
      deep: true
    },
    selectedFile: {
      handler: function () {
        console.log('made it, ROUND TRIP FILE')
      }
    },
    baudRate: {
      handler: function () {
        if (this.baudRate > 100) {
          ipcRenderer.send('to_bgWin', { 'function': 'setBaud',
            'params': {
              rate: this.baudRate
            }
          })
        }
      }
    }
  },
  methods: {
    // This functiuon checks for key terms in feedback from the WOWPOW to
    // determine if it is a loading message or not
    isThisAnUpdate (message) {
      if (message.search('Packets') >= 0) {
        return true
      } else {
        return false
      }

      if (message.search('of') >= 0) {
        if (message.search('date') >= 0) {
          return true
        } else {
          return false
        }
      } else {
        return false
      }
    },

    // This currently unused function can be used to swap highlighting fields
    // pass in a COM entry ID and if it matches the currently selected port
    // highlight it, if it doesnt, unhighlight it.
    portEntryClass: function (id) {
      if (id == this.selectedPort) {
        return 'selectedPortClass'
      } else {
        return 'unselectedPortClass'
      }
    },
    // this function receives a HTML dom object (<option> in this case), saves
    // the value as the selectedPort, and sends the port to the openPort function
    selectPort (e) {
      var port = e.target.value
      this.selectedPort = port
      this.openPort(port)
    },
    // this function accepts a text string name/path of a COM port and sends it
    // to the bgWin setPort function
    openPort (port) {
      this.WOWPOWInterface.feedback = []
      ipcRenderer.send('to_bgWin',
        { 'function': 'setPort',
          'params': { 'port': port
          } })
    },
    // this function requests a complete send/resend of all available COM ports
    // by sending a getPorts request to the bgWin
    refreshPortList () {
      ipcRenderer.send('to_bgWin',
        { 'function': 'getPorts',
          'params': {
          } })
    },
    // this function simple empties the feedback queue shown in the feedback box
    feedbackClear () {
      this.WOWPOWInterface.feedback = []
    },
    /**
      *@description This method returns a full path for a button icon and insures it is loaded.
      *@param {String} icon - Full name of the button icon
      */
    buildImageUrl (icon) {
      return require(`../../assets/icons/${icon}`)
    },
    /**
      *@description This function applies a class to an object (specifically an image). Could be done differently, but this works
      *@param {String} selectedClass - The name of the class to be applied to the image/icon/object
      *@param {String} imageId - The ID of the image or object to styled with the provided class
      */
    svgOnReady (selectedClass, imageId) {
      $('#' + imageId).addClass(selectedClass)
    },
    Nav: function (place) {
      this.$root.$emit('Navigate', place)
    },
    getFileName: function (filename) {
      this.selectedFile = filename
    },
    // This function takes in the file location DOM asset, confirms that a
    // a selection was made. after this is stores the file path and name, and
    // sends it to the background window for later use.
    onFileChange (e) {
      var files = e.target.files || e.dataTransfer.files
      if (!files.length) { return }
      this.selectedFilePath = files[0].path
      this.selectedFileName = files[0].name
      ipcRenderer.send('to_bgWin', { 'function': 'selectFile',
        'params': {
          path: this.selectedFilePath,
          name: this.selectedFileName
        } })
    }
  },
  mounted () {
    var that = this
    ipcRenderer.send('to_sessWin',
      { 'function': 'CheckAdvancedStatus',
        'params': {} })

    ipcRenderer.on('AdvancedStatusFromDB', (event, arg) => {
      var status = arg.AdvancedEnable
      if (status == true) {
        this.AdvanceChecked = true
      } else {
        this.AdvanceChecked = false
      }
    })

    ipcRenderer.on('PortConfirmed', (event, arg) => {
      var portFound = arg['portFound']
      if (portFound == true) {
        this.portApproved = true
      } else {
        this.portApproved = false
      }
    })

    // this activates a listener for the showCOMS event.
    // when triggered the existing list of PORTS is cleared and the argument
    // COM_list is looped through and each entry is added to the displayed port
    // list. the first port shown is then opened.
    ipcRenderer.on('showCOMS', (event, arg) => {
      that.WOWPOWInterface.portList = []
      console.log(arg['COM_list'])
      if (this.AdvanceChecked == true) {
        for (var i = 0; i < arg['COM_list'].length; i++) {
          that.WOWPOWInterface.portList.push(arg['COM_list'][i]['path'])
          if (i == 0) {
            that.openPort(arg['COM_list'][i]['path'])
          }
        }
      }
      that.COMlistExists = true
    })

    // this activates a listener for the WOWPOWFeedback function. When triggered
    // this function checks to see if the message received was sent from the PC
    // or received from the WOWPOW, adds a text message identifying this status
    // and adds it to the feedback queue for display.
    ipcRenderer.on('WOWPOWFeedback', (event, arg) => {
      if (arg['sent']) {
        var message = /*'Sent: ' + */arg['result']
      } else {
        var message = /*'Received: ' + */arg['result']
      }

      // check to see if the message that was received is an updating message
      if (that.isThisAnUpdate(message)) {
        // if it is loop through and remove any update messages from the
        // feedback array which is shown.
        for (var i = 0; i < that.WOWPOWInterface.feedback.length; i++) {
          if (that.isThisAnUpdate(that.WOWPOWInterface.feedback[i])) {
            that.WOWPOWInterface.feedback.splice(i, 1)
          }
        }
      }

      that.WOWPOWInterface.feedback.push(message)
      // this call to the display window (current window) allows us to "bubble"
      // this command up from this private lambda function
      ipcRenderer.send('to_win',
        { 'function': 'scrollBottom',
          'params': {} })
      // that.WOWPOWInterface.portList = arg['COM_list']
    })

    // this activates a listenere for the "clearFeedback" function. When
    // triggered the feedback queue is emptied
    ipcRenderer.on('clearFeedback', (event, arg) => {
      that.WOWPOWInterface.feedback = []
    })

    ipcRenderer.on('sendText', (event, arg) => {
      console.log('we hear send text, do we send it?')
      console.log(that.textCommand)
      ipcRenderer.send('to_bgWin',
        { 'function': 'sendText',
          'params': { 'command': that.textCommand } })
    })

    ipcRenderer.on('EnteredEraseMode', (event, arg) => {
      console.log("we entered erase mode")
      that.notInWriteMode = 2
    })

    ipcRenderer.on('EnteredWriteMode', (event, arg) => {
      that.notInWriteMode = 0
    })

    ipcRenderer.on('ExitWriteMode', (event, arg) => {
      that.notInWriteMode = 1
    })

    // this activates a listener for the "scollBottom" function and when
    // triggered this retrieves the feedback box from the DOM and scrolls it
    // to the total length of the overflowed content (thereby landing at the
    // latest message)
    ipcRenderer.on('scrollBottom', (event, arg) => {
      document.getElementById('feedback_box').scroll(0, document.getElementById('feedback_box').scrollHeight)
      // .$refs.anAccount
    })
  },
  created () {
    ipcRenderer.removeAllListeners('showCOMS')
    ipcRenderer.removeAllListeners('AdvancedStatusFromDB')
    // as soon as this page has loaded far enough to have its data model in
    // request a list of available COM ports from the backend window
    ipcRenderer.send('to_bgWin',
      { 'function': 'getPorts',
        'params': {
        } })
  },
  deactivated () {
    ipcRenderer.removeAllListeners('showCOMS')
    ipcRenderer.removeAllListeners('AdvancedStatusFromDB')
    ipcRenderer.removeAllListeners('PortConfirmed')
    ipcRenderer.removeAllListeners('WOWPOWFeedback')
    ipcRenderer.removeAllListeners('clearFeedback')
    ipcRenderer.removeAllListeners('sendText')
    ipcRenderer.removeAllListeners('EnteredWriteMode')
    ipcRenderer.removeAllListeners('ExitWriteMode')
    ipcRenderer.removeAllListeners('scrollBottom')
    // when this window is deactivated/closed send a closePort call to the
    // backend window to make sure we dont have orhpaned memory objects.
    ipcRenderer.send('to_bgWin',
      { 'function': 'closePort',
        'params': {
        } })
  }
}

</script>
<style>
.easyIndicator{
    margin-bottom:0px !important;
}

.feedbackLeftControls{
    display:inline;
    float:left;
}

.feedbackRightControls{
    display:inline;
    float:right;
}

.selectedPortClass{
    padding-left:10px;
    background-color:grey;
    color:white;
}

.feedbackBox{
    height:100px;
    overflow-y:scroll;
    background-color:white;
}

.feedbackLine{
    font-size: var(--card_text);
}


.selectProgramSDMenu{
    display:flex;
    justify-content: center;
    flex-direction: row;
    flex-wrap: wrap;
    height:100%;

}

.programSDSteps{
    display:flex;
    flex-direction: column;
    font-size: var(--page_content_text);
}

.programSDSingleStep{
    display:flex;
    align-items: center;

    flex-flow: row;
    width: 85%;
    align-self: center;
}

.programSDSingleStep:not(:last-child){
    margin-bottom:3%;
    border-bottom:1px solid var(--page_background);
}

.stepIdentifier{
    margin-right: 6%;
    border-radius: 100%;
    padding: 10px;
    height:30px;
    width:30px;
}

.stepUpcoming{
    color:var(--color_accent);
    border:4px;
    border-color:var(--color_accent);
    border-style: solid;
    background-color:var(--text_light);
}

.stepCurrent{
    color:var(--text_light);
    border:4px;
    border-color:var(--color_accent);
    border-style: solid;
    background-color:var(--color_accent);
}

.stepDone{
    color:var(--color_accent);
    border:4px;
    border-color:var(--color_accent);
    border-style: solid;
    background-color:var(--color_accent);
}

.stepText{
    margin-right:3%;
    color:var(--text_medium);
    vertical-align: middle;
    text-align: left;
    width:40%;
}

.stepForm{
    font-size:var(--card_text);
    float:right;
    text-align:right;
    width:51%;
    align:right;
}

.fileUpdateFileContainer {
    overflow: hidden;
    position: relative;
    width:165px !important;
    float:right;
}

.fileUpdateFileContainer [type=file] {
    cursor: inherit;
    display: block;
    font-size: 999px;
    filter: alpha(opacity=0);
    min-height: 100%;
    min-width: 100%;
    opacity: 0;
    position: absolute;
    right: 0;
    text-align: right;
    top: 0;
}

.fileUpdateFileContainer {
    background: var(--base_dark);
    border:1px solid var(--color_accent);
    padding: .25em;
    font-size: var(--page_content_text);
    text-align:center;
    background: var(--color_accent);
    color:var(--text_light);
    height:31px;
}

.fileUpdateFileContainer:hover{
    -moz-box-shadow:    0px 0px 0px 5px  var(--color_light);
    -webkit-box-shadow: 0px 0px 0px 5px  var(--color_light);
    box-shadow:         0px 0px 0px 5px  var(--color_light);
}

.fileUpdateFileContainer [type=file] {
    cursor: pointer;
    background: var(--color_accent);
    color:var(--text_light);
}

.driveSelect{
    height:1.925em;
    font-size: var(--page_content_text);
    line-height: 31px;
    vertical-align: middle;
    border:1px solid var(--base_dark);
    background-color: var(--text_light);
    width:200px;
}

.driveSelect:hover{
    -moz-box-shadow:    0px 0px 0px 5px  var(--color_light);
    -webkit-box-shadow: 0px 0px 0px 5px  var(--color_light);
    box-shadow:         0px 0px 0px 5px  var(--color_light);
}

.updateFileSelectName{
    float:left !important;
    text-overflow: ellipsis;
    overflow:hidden;
}

.updateFileSelectName{
    height:1.925em;
    width:250px;
    text-align:center;
    line-height: 1.925em;
    vertical-align: middle;
    border:1px solid var(--base_dark);
    background-color: var(--text_light);
    font-size: var(--page_content_text);
    overflow:hidden;
}

.driveRefresh{
    position:relative;
    top:1px;
    color:var(--base_dark);

}

.driveRefresh:hover{
    color:var(--color_dark);
}

#jumperBar_1{
    background-color:var(--color_accent);
    width: 5px;
    height: 114px;
    position:absolute;
    left: 25.5%;
    top: 33.75%;
}

#jumperBar_2{
    background-color:var(--color_accent);
    width: 5px;
    height:115px;
    position:absolute;
    left: 25.5%;
    top: 57%;
}

#loadingBarBox{
    display:flex;
    flex-flow: column;
    width:100%;
    height:50px;

}

.loadingRingClass{
    top:19%;
    left:43.5%;
    position:absolute;
    display:inline-block;
    height: 400px;
}

#loadingRing{
    stroke:var(--color_medium);
    fill:var(--color_accent);
    animation-direction: alternate;
    -webkit-animation: CircleSpin 8s linear infinite;
    -moz-animation: CircleSpin 8s linear infinite;
}

@keyframes CircleSpin {
    0% {
      transform: rotate(0deg);
    }
    25% {
      transform: rotate(-90deg);
    }
    50% {
      transform: rotate(-180deg);
    }
    75% {
      transform: rotate(-270deg);
    }
    100% {
      transform: rotate(-360deg);
    }
}

@keyframes PulseShimmer {
    0% {
      transform: scale(1);
      opacity: 1;
    }
    50%{
        transform: scale(1.1);
        opacity:1;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

@media (max-width: 1399px)  {
    #jumperBar_1{
        top: 35.5%;
    }

    #jumperBar_2{
        top: 60%;
    }
}

</style>
