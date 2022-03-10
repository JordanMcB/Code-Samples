/***
Copyright Train Control Systems Inc. 2021
Document: WOWPOWIndicator.vue
Purpose: This file generates a loading bar that sends out messages at completion
        and receives statuses in percentages (0.0 - 1.0) which update how full
        the loading bar is.
Developer: JMM
Date: Dec 17th 2021
***/
<template>
    <div class="IndicatorContainer">
        <div class="WOWPOWIndicator">
            <div :class="GreenCSS">
                <div class="WOWPowHighlight">
                </div>
            </div>
            <div :class="YellowCSS">
                <div class="WOWPowHighlight">
                </div>
            </div>
            <div :class="RedCSS">
                <div class="WOWPowHighlight">
                </div>
            </div>
        </div>
        <div :class="TextCSS"><div :class="TextEntryCSS"><br>Searching...</div>
        </div>
    </div>
</template>

<script>
const { ipcRenderer } = require('electron')
/**
*
* @module components/UI/WOWPOWIndicator
* @description This component generates a horizontal stoplight which indicates the current
* status of the connection to the hardware WOWPOW device. Green is connected, Yellow is attempting, Red is 
* all COM ports have been checked and no hardware found.
*/
export default {
  name: 'WOWPOWIndicator',
  props: {},
  data: function () {
    return {
      GreenOptions: ['WOWPOWGreen', 'WOWPOWGreenHighlight'],
      YellowOptions: ['WOWPOWYellow', 'WOWPOWYellowHighlight'],
      RedOptions: ['WOWPOWRed', 'WOWPOWRedHighlight'],
      TextOptions: ['TextStatusNone', 'TextStatusOut', 'TextStatusIn'],
      TextEntryOptions: ['TextEntryHide', 'TextEntryShow'],
      GreenCSS: 'WOWPOWDot WOWPOWGreen',
      YellowCSS: 'WOWPOWDot WOWPOWYellow',
      RedCSS: 'WOWPOWDot WOWPOWRed',
      TextCSS: "TextStatusOut",
      Status: 'None',
      TextEntryCSS: "TextStatusNone"
    }
  },
  created () {},
  watch: {
    Status: {
      handler: function (val) {
        switch (val) {
          case 'None':
            this.GreenCSS = 'WOWPOWDot ' + this.GreenOptions[0]
            this.YellowCSS = 'WOWPOWDot ' + this.YellowOptions[0]
            this.RedCSS = 'WOWPOWDot ' + this.RedOptions[0]
            break
          case 'Red':
            this.GreenCSS = 'WOWPOWDot ' + this.GreenOptions[0]
            this.YellowCSS = 'WOWPOWDot ' + this.YellowOptions[0]
            this.RedCSS = 'WOWPOWDot ' + this.RedOptions[1]
            this.TextCSS = this.TextOptions[2]
            this.TextEntryCSS = this.TextEntryOptions[0]
            break
          case 'Green':
            this.GreenCSS = 'WOWPOWDot ' + this.GreenOptions[1]
            this.YellowCSS = 'WOWPOWDot ' + this.YellowOptions[0]
            this.RedCSS = 'WOWPOWDot ' + this.RedOptions[0]
            this.TextCSS = this.TextOptions[2]
            this.TextEntryCSS = this.TextEntryOptions[0]
            break
          case 'Yellow':
            this.GreenCSS = 'WOWPOWDot ' + this.GreenOptions[0]
            this.YellowCSS = 'WOWPOWDot ' + this.YellowOptions[1]
            this.RedCSS = 'WOWPOWDot ' + this.RedOptions[0]
            this.TextCSS = this.TextOptions[1]
            this.TextEntryCSS = this.TextEntryOptions[1]
            break
        }
      },
      deep: true
    }
  },
  methods: {},
  mounted () {
    var that = this
    ipcRenderer.on('WOWPOWIndicatorStatus', (event, arg) => {
      that.Status = arg.status
    })
  },
  beforeDestroy () {
    ipcRenderer.removeAllListeners('WOWPOWIndicatorStatus')
  }
}

</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style>

.WOWPOWDot{
    border: .5px solid black;
    height: 20px;
    width: 20px;
    border-radius: 10px;
    display: inline-block;
    margin-left:5px;
    margin-right:5px;
    margin-top:4px;
    -webkit-box-shadow: 1.5px 1.5px 5px -1px #000000;
    dbox-shadow: 1.5px 1.5px 5px -1px #000000;
}

.WOWPOWIndicator{
    z-index:20;
    height:30px;
    width:minimum;
    position:relative;
    border: .5px solid black;
    border-radius: 15px;
    display: inline-block;
    background:#c0c2c1;
    -webkit-box-shadow: 2.5px 2.5px 4px -2px #000000;
    dbox-shadow: 2.5px 2.5px 4px -2px #000000;
}

.WOWPowHighlight{
   height: 6px;
   width: 6px;
   border-radius: 1px;
   border: 0px solid white;
   background:white;
   position:relative;
   left: 4px;
   top: 2px;
   -webkit-filter: blur(1.5px);
  -moz-filter: blur(1.5px);
  -o-filter: blur(1.5px);
  -ms-filter: blur(1.5px);
  filter: blur(1.5px);
}

.WOWPOWGreen{
    background:#004004

}

.WOWPOWGreenHighlight{
   background:#00e60e;
}

.WOWPOWYellow{
    background:#403e00;
}

.WOWPOWYellowHighlight{
    animation-name: blinken;
    animation-duration: .2s;
    animation-iteration-count: infinite;
    animation-direction: alternate;
    animation-timing-function:steps(2);
}

@keyframes blinken {
    from{
        background:#403e00;
    }
    50%{
        background:#e6df00;
    }
    to{
        background:#e6df00;
    }

}

.WOWPOWRed{
    background:#400000;
}

.WOWPOWRedHighlight{
    background:#e60000;
}

.TextStatusNone{
    display:none;
}

.IndicatorContainer{
    float:right;
    width:94px;
    position:relative;
}

.TextStatusOut{
    z-index:1;
    top:-28px;
    height:50px;

    position:relative;
    border: .5px solid black;
    border-radius: 15px;
    display: block;
    background:#c0c2c1;
    position:relative;
    -webkit-box-shadow: 2.5px 2.5px 4px -2px #000000;
    dbox-shadow: 2.5px 2.5px 4px -2px #000000;
    display: flex;
    justify-content: end;
    align-items: flex-end;
    flex-direction: column;
    align-content:flex-end;

    animation-name: slideOut;
    animation-duration: 1s;
}

.TextStatusIn{
    z-index:1;
    top:-28px;
    height:26px;
    width:minimum;
    border: .5px solid black;
    border-radius: 15px;
    display: block;
    background:#c0c2c1;
    position:relative;
    -webkit-box-shadow: 2.5px 2.5px 4px -2px #000000;
    dbox-shadow: 2.5px 2.5px 4px -2px #000000;
    display: flex;
    justify-content: end;
    align-items: flex-end;
    flex-direction: column;
    align-content:flex-end;

    animation-name: slideIn;
    animation-duration: 1s;
}

.TextEntryHide{
    position:relative;
    bottom:10px;
    display: inline !important;
    align-self: flex-end;
    animation-name: TextIn;
    animation-duration: 1s;
}

.TextEntryShow{
    position:relative;
    bottom:-13px;
    display: inline !important;
    align-self: flex-end;
    animation-name: TextOut;
    animation-duration: 1s;
}

@keyframes slideOut {
    from{
        height:28px;
    }
    to{
        height:50px;
    }

}

@keyframes slideIn {
    from{
        height:50px;
    }
    to{
        height:26px;
    }

}

@keyframes TextIn {
    from{
        bottom:-13px;
    }
    to{
        bottom:10px;
    }
}

@keyframes TextOut {
    from{
        bottom:10px;
    }
    to{
        bottom:-13px;
    }
}

</style>
