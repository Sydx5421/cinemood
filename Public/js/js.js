$(document).ready(function(){

    var catSelect = $("#categorySelect");

    catSelect.change(function(){
        // alert($(this).val());
        $('textarea').removeAttr("disabled");
        $('button[type="submit"]').removeAttr("disabled");
    });

});