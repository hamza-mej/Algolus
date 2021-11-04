$(document).ready(function (){
    $('.editBtnProduct').on('click', function (){
        $('#myModalEditProduct').modal('show');


        var Id = $(this).attr("data-id");
        var Url = "/productedit"+Id
        $('#formEdit').attr("action",Url);

        // $('#name').val($(this).attr("data-id"));
        $('#name').val($(this).attr("data-name"));
        $('#price').val($(this).attr("data-price"));

        $('#description').val($(this).attr("data-description"));
    })
});

$(document).ready(function (){
    $('.editBtnCategory').on('click', function (){
        $('#myModalEditCategory').modal('show');


        var Id = $(this).attr("data-id");
        var Url = "/categoryedit"+Id
        $('#formEditCategory').attr("action",Url);

        // $('#name').val($(this).attr("data-id"));
        $('#name').val($(this).attr("data-name"));
        $('#description').val($(this).attr("data-description"));
    })
});

// $(document).ready(function (){
//     $('.deleteBtn').on('click', function (){
//         $('#myModalDelete').modal('show');
//
//         var Id = $(this).attr("delete-id");
//         var Url = "/delete"+Id
//         $('#formDelete').attr("action",Url);
//     })
// })

// $(document).ready(function (){
//     $('.deleteBtn').on('click', function (){
//         Swal.fire(
//             'good job!',
//             'y',
//             'success'
//         )
//     })
// });


function sweetalertclick(e){
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: '<a href="{{ path(\'app_delete\', {id: product.id}) }}">Yes, delete it!</a>',
        // footer: '<a href="{{ path(\'app_delete\', {id: product.id}) }}">Why do I have this issue?</a>'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire(
                'Deleted!',
                'Your file has been deleted.',
                'success'
            )
        }
    })
}

// $(document).ready(function (){
//
//     $(document).on('click', ".deleteBtn", function (e){
//         var Id = $(this).attr("data-id");
//         var Url = "/delete"+Id;
//         sweetalertclick(Id, Url);
//         e.preventDefault();
//     });
//
//
//     function sweetalertclick(Id, Url) {
//         swal({
//             title: "Are you sure?",
//             text: "You will not be able to recover this Job Title!",
//             type: "warning",
//             showCancelButton: true,
//             confirmButtonColor: "#DD6B55",
//             confirmButtonText: '<a href="Url">Yes, delete it!</a>',
//             showLoaderOnConfirm: true,
//         }).then((result) => {
//             if (result.isConfirmed) {
//                 Swal.fire(
//                     'Deleted!',
//                     'Your file has been deleted.',
//                     'success'
//                 )
//             }
//         })
//     };
// });

// $(document).ready(function (){
//
//     $(document).on('click', ".deleteBtn", function (e){
//         var Id = $(this).attr("data-id");
//         var Url = "/delete"+Id;
//         var btn =   $('#btn').attr("href",Url);
//         sweetalertclick(btn);
//         e.preventDefault();
//     });
//
//
//     function sweetalertclick(Id, Url) {
//         swal({
//             title: "Are you sure?",
//             text: "You will not be able to recover this Job Title!",
//             type: "warning",
//             showCancelButton: true,
//             confirmButtonColor: "#DD6B55",
//             confirmButtonText: "Yes, delete it!",
//             showLoaderOnConfirm: true,
//             // closeOnConfirm: false,
//             preConfirm: function (){
//                 return new Promise(function (resolve){
//                     $.ajax({
//                         url: btn,
//                         type: "POST",
//                         dataType: 'json',
//                     }).done(function (response){
//                         swal('Deleted!', response.message, response.status);
//
//                     }).fail(function (){
//                         swal('Oops', 'something' );
//                     });
//                 });
//             },
//         })
//     };
// });

// function myFunction(btn) {
//     var url = $(btn).data('url');
//     swal({
//         title: "Are you sure?",
//         text: "You will not be able to recover this Job Title!",
//         type: "warning",
//         showCancelButton: true,
//         confirmButtonColor: "#DD6B55",
//         confirmButtonText: "Yes, delete it!",
//         closeOnConfirm: false,
//         preConfirm: function (){
//             return new promise(function (resolve){
//                 $.ajax({
//                     url: "/delete"+Id,
//                     type: "POST",
//                     data:,
//                 })
//             })
//         }
//         }
//     };

// function sweetalertclick(e){
//         Swal.fire({
//                 title: "Are you sure!",
//                 type: "error",
//                 confirmButtonClass: "btn-danger",
//                 confirmButtonText: "Yes!",
//                 showCancelButton: true,
//             },
//             function() {
//                 $.ajax({
//                     type: "POST",
//                     url: "{{path(\\'app_delete\\')}}",
//                 });
//             });
// };


// const id = e.target.attribute('delete-id');
// fetch(`/delete${id}`)
//     .then(res => window.location.reload());


// Swal.fire(
//     'Good job!',
//     'You clicked the button!',
//     'success'
// )