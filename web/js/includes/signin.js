signInWidget=function() {
    let widget=$$.query('div#msi');    
    if (widget.classList.contains('barcode')) {
        widget.classList.remove('barcode');
    }
}
