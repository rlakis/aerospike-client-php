<script>
    window.onload = function() {
        if (window.opener) {
            window.opener.location.reload();
            window.close();
        }
        else {
            if (typeof top.dg !== 'undefined' && top.dg.isOpen() == true) {
                top.dg.closeFlow();
                top.window.location.reload();
                return true;
            }
        }
    };
</script>