<script type="text/javascript">
var articleId=0;
var articles = document.querySelectorAll("article");
var len=articles.length;
for (var x=0; x<len; x++) {
    articles[x].addEventListener("click", function(e){
        if (articleId>0) {
            document.getElementById(articleId).classList.remove('selected');
        }
        articleId=this.id;
        this.classList.add("selected");
        e.preventDefault();                
    });
    
    
};

function EAD(){}
</script>

