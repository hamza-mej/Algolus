
$(document).ready(function () {
    $('.editBtnUser').on('click', function () {
        $('#myModalEditUser').modal('show');

        var Id = $(this).attr("data-id");
        var Url = "/editUser" + Id
        $('#formEditUser').attr("action", Url);

        $('#email').val($(this).attr("data-email"));
        $('#firstName').val($(this).attr("data-FirstName"));
        $('#lastName').val($(this).attr("data-LastName"));
    })
});

$(document).ready(function () {
    $('.editBtnBlog').on('click', function () {
        $('#myModalEditBlog').modal('show');

        var Id = $(this).attr("data-id");
        var Url = "/blog_edit" + Id
        $('#formEditBlog').attr("action", Url);

        $('#BlogTitle').val($(this).attr("data-title"));
        $('#BlogDescription').val($(this).attr("data-description"));
        $('#BlogImage').val($(this).attr("data-image"));
    });

    $('.editBtnUser').on('click', function () {
        $('#myModalEditUser').modal('show');

        var Id = $(this).attr("data-id");
        var Url = "/editUser" + Id
        $('#formEditUser').attr("action", Url);

        $('#email').val($(this).attr("data-email"));
        $('#firstName').val($(this).attr("data-FirstName"));
        $('#lastName').val($(this).attr("data-LastName"));
    });

    $('.editBtnAboutUs').on('click', function () {
        $('#myModalEditAboutUs').modal('show');

        var Id = $(this).attr("data-id");
        var Url = "/edit_about_us" + Id
        $('#formEditAboutUs').attr("action", Url);

        $('#AboutUsTitle').val($(this).attr("data-title"));
        $('#AboutUsDescription').val($(this).attr("data-description"));
        $('#AboutUsContent').val($(this).attr("data-content"));
    })

    $('.editBtnHomeBlog').on('click', function () {
        $('#myModalEditHomeBlog').modal('show');

        var Id = $(this).attr("data-id");
        var Url = "/edit_home_blog" + Id
        $('#formEditHomeBlog').attr("action", Url);

        $('#HomeBlogTitle').val($(this).attr("data-title"));
        $('#HomeBlogDescription').val($(this).attr("data-description"));
        $('#HomeBlogContent').val($(this).attr("data-content"));
    })

    $('.editBtnBanner').on('click', function () {
        $('#myModalEditBanner').modal('show');

        var Id = $(this).attr("data-id");
        var Url = "/edit_banner" + Id
        $('#formEditBanner').attr("action", Url);

        $('#BannerSupTitle').val($(this).attr("data-supTitle"));
        $('#BannerTitle').val($(this).attr("data-title"));
        $('#BannerDescription').val($(this).attr("data-description"));
    })

    $('.editBtnSecondBanner').on('click', function () {
        $('#myModalEditSecondBanner').modal('show');

        var Id = $(this).attr("data-id");
        var Url = "/edit_second_banner" + Id
        $('#formEditSecondBanner').attr("action", Url);

        $('#SecondBannerTitle').val($(this).attr("data-title"));
        $('#SecondBannerDescription').val($(this).attr("data-description"));
    })
});


// $(document).ready(function (){
//     $('.editBtnCategory').on('click', function (){
//         $('#myModalEditCategory').modal('show');
//
//
//         var Id = $(this).attr("data-id");
//         var Url = "/categoryedit"+Id
//         $('#formEditCategory').attr("action",Url);
//
//         // $('#name').val($(this).attr("data-id"));
//         $('#name').val($(this).attr("data-name"));
//         $('#image').val($(this).attr("data-image"));
//         // $('#description').val($(this).attr("data-description"));
//     })
// });


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


function sweetalertclick(e) {
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