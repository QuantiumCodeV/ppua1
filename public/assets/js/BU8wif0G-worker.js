(function(){"use strict";self.addEventListener("message",()=>{let e=new Date().getTime();const t=1e4;setInterval(()=>{const s=new Date().getTime();s>e+2*t&&self.postMessage("wakeup"),e=s},t),setInterval(()=>{self.postMessage("ping")},15e3)})})();
//# sourceMappingURL=https://smaps.pumble.com/assets/source-maps/BU8wif0G-worker.js.map
