(function(){"use strict";onmessage=function(t){let e=null;const{data:{type:s,ms:a=1e3/30}={}}=t;switch(s){case"SET":{e=setTimeout(()=>{postMessage({type:"TICK"})},a);break}default:clearTimeout(e)}}})();
//# sourceMappingURL=https://smaps.pumble.com/assets/source-maps/DvP3skeR-worker.js.map
