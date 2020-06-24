$(document).ready(function(){

    //form select management when adding a category to a movie
    var catSelect = $("#categorySelect");
    catSelect.change(function(){
        // alert($(this).val());

        if($('textarea').val() != ""){
            alert("Si vous changez de catégorie votre commentaire sera effacé. Continuer ?");
            $('textarea').val('');
        }

        $('textarea').removeAttr("disabled");
        $('button[type="submit"]').removeAttr("disabled");
    });

});