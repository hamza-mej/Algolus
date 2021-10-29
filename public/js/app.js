$(document).ready(function (){
    $('.editBtn').on('click', function (){
        $('#myModal').modal('show');


        var Id = $(this).attr("data-id");
        var Url = "/edit"+Id
        $('#formEdit').attr("action",Url);

        // $('#name').val($(this).attr("data-id"));
        $('#name').val($(this).attr("data-name"));
        $('#price').val($(this).attr("data-price"));

        $('#description').val($(this).attr("data-description"));
    })
})

$(document).ready(function (){
    $('.deleteBtn').on('click', function (){
        $('#myModalDelete').modal('show');

        var Id = $(this).attr("delete-id");
        var Url = "/delete"+Id
        $('#formDelete').attr("action",Url);
    })
})