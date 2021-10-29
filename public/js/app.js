$(document).ready(function (){
    $('.editBtn').on('click', function (){
        $('#myModal').modal('show');


        var Id = $(this).attr("data-id");
        var Url = "/edit"+Id
        $('#form').attr("action",Url);

        $('#name').val($(this).attr("data-id"));
        $('#name').val($(this).attr("data-name"));
        $('#price').val($(this).attr("data-price"));
        $('#description').val($(this).attr("data-description"));git add -A
    })
})

// $(document).ready(function (){
//     $('.addBtn').on('click', function (){
//         $('#myModalAdd').modal('show');
//
//     })
// })