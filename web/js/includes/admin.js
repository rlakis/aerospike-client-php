<script>
if(typeof userRaw==='string'){
    var wrapper = document.getElementById("userDIV");
    try {
        var data = JSON.parse(userRaw);                    
        var tree = jsonTree.create(data, wrapper);
        tree.expand();
    } catch (e) {
        console.log(e);
    }
}

</script>