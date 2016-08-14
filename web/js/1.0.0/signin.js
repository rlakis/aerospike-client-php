    var jsId = document.cookie.match(/PHPSESSID=[^;]+/);
    if(jsId != null) {
        if (jsId instanceof Array)
            jsId = jsId[0].substring(10);
        else
            jsId = jsId.substring(10);
    }
    
    var wio = io.connect("io.mourjan.com:1313", {transports: ['websocket'], 'force new connection': false});

    wio.on('signin', function(d){
        if (d.barcode!=='undefined') {
            window.location.reload();
        }
    });

    wio.emit("regs",{sid:jsId});