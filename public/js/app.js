
// $(document).ready(function () {
//     $('.editBtnUser').on('click', function () {
//         $('#myModalEditUser').modal('show');
//
//         var Id = $(this).attr("data-id");
//         var Url = "/editUser" + Id
//         $('#formEditUser').attr("action", Url);
//
//         $('#email').val($(this).attr("data-email"));
//         $('#firstName').val($(this).attr("data-FirstName"));
//         $('#lastName').val($(this).attr("data-LastName"));
//     })
// });




$(document).ready(function () {
    /* by default hide all radio_content div elements except first element */
    $(".content .radio_content").hide();
    $(".content .radio_content:first-child").show();

    /* when any radio element is clicked, Get the attribute value of that clicked radio element and show the radio_content div element which matches the attribute value and hide the remaining tab content div elements */
    $(".radio_wrap").click(function(){
        var current_raido = $(this).attr("data-radio");
        $(".content .radio_content").hide();
        $("."+current_raido).show();
    })

});


// function text(x){
//     if(x == 0) {
//         document.getElementById('myForm').style.display = 'block';
//         document.getElementById('myForm2').style.display = 'none';
//     }
//
//     if(x == 1) {
//         document.getElementById('myForm').style.display = 'none';
//         document.getElementById('myForm2').style.display = 'block';
//     }
//     return;
// }

// $('#yesCheck').click(function() {
//     document.getElementById('ifYes').style.display = 'none';
//     else
// });
// $('#noCheck').click(function() {
//     document.getElementById('ifYes').style.display = 'none';
// });


$(document).ready(function () {

    const Table = new Array();
    // $('.addField').on('click', function () {
    //
    //         // Table.color = $('.selectColor option:selected').text();
    //         // Table.size = $('.selectSize option:selected').text();
    //         // Table.qty = $('.selectQty ').val();
    //
    //         Table.push({  "ProductSelected" : $('.selectProduct option:selected:last').val(),
    //                       "color" : $('.selectColor option:selected:last').text(),
    //                       "size" : $('.selectSize option:selected:last').text(),
    //                       "qty" : $('.selectQty:last ').val()
    //                     });
    //         console.log(Table);
    //
    //         str = JSON.stringify(Table);
    //
    //         document.getElementById("demo").innerHTML = str;
    //     })


    $('.addField').on('click', function () {

        // Table.color = $('.selectColor option:selected').text();
        // Table.size = $('.selectSize option:selected').text();
        // Table.qty = $('.selectQty ').val();

        Table.push({  "ProductSelected" : $('.selectProduct option:selected:last').val(),
                      "color" : $('.selectColor option:selected:last').text(),
                      "size" : $('.selectSize option:selected:last').text(),
                      "qty" : $('.selectQty:last ').val()
                    });

        str = JSON.stringify(Table);

        document.getElementById("demo").innerHTML = str;
    })

    $('.removeField').on('click', function () {

        if ( Table.find($('.selectProduct:last').val()) && Table.includes($('.selectColor:last').val())
            && Table.includes($('.selectSize:last').val())  && Table.includes($('.selectQty:last').val())  ){
            // Table.pop();
            alert('delete')
        } else {
            // return Table
            alert('ok')
        }

        //
        // if ( Table.ProductSelected === ($('.selectProduct:last').val()) && Table.color === ($('.selectColor:last').val())
        //     && Table.size === ($('.selectSize:last').val())  && Table.qty ===($('.selectQty:last').val())  ){
        //     alert('delete')
        // } else {
        //     // return Table
        //     alert('stay')
        // }

        // if (
        //     // $('.selectProduct:last').val() &&
        //     $('.selectColor:last').val()
        //     && $('.selectSize:last').val() && $('.selectQty:last').val() )
        // {
        //     Table.pop({
        //         // "ProductSelected" : $('.selectProduct option:selected:last').val(),
        //         "color" : $('.selectColor option:selected:last').text(),
        //         "size" : $('.selectSize option:selected:last').text(),
        //         "qty" : $('.selectQty:last ').val()
        //     });
        // }else
        //     Table.pop();



        str = JSON.stringify(Table);

        document.getElementById("demoDelete").innerHTML = str;
    })


    // var Url = "/create-stock"
    // $('#formEditUser').attr("action", Url);

    $('.addToController').on('click',function (e) {
        // e.preventDefault();
        if ( $('.selectProduct').val() && $('.selectColor').val()
            && $('.selectSize').val() && $('.selectQty').val() )
        {
            Table.push({  "ProductSelected" : $('.selectProduct option:selected:last').val(),
                "color" : $('.selectColor option:selected:last').text(),
                "size" : $('.selectSize option:selected:last').text(),
                "qty" : $('.selectQty:last ').val()
            });
        }


        var dataSend = JSON.stringify(Table);
        // console.log(dataSend);

        $.ajax({
            url: "/create-stock",
            type: "POST",
            data: dataSend,
            success: function (data) {
                // // if(data == dataSend) console.log("ok");
                console.log("OK");
                // alert('ok')
            },
            error: function (data) {
                alert("fail");
            }
        });
    });

});


// $('[name="size[]"]').clone().appendTo('#testSize');
// $('.qtyProduct').clone().find('input').val('').end().appendTo('#testQty');
// // $('#firstProduct .testRemove').clone().appendTo('#testRemove');

$(document).ready(function () {
    $('.addField').on('click', function () {
        $('#firstProduct [name="color[]"]').clone().appendTo('#testColor');
        $('#firstProduct [name="size[]"]').clone().appendTo('#testSize');
        $('#firstProduct .qtyProduct').clone().find('input').val('').end().appendTo('#testQty');
        $('#firstProduct .testRemove').clone().appendTo('#testRemove');
    })

    $('.removeField').on('click', function () {
        $('#testColor [name="color[]"]').last().remove();
        $('#testSize [name="size[]"]').last().remove();
        $('#testQty [name="qty"]').last().remove();
        $('#testRemove .testRemove').last().remove();
    })
});

$(document).ready(function () {
    $('.addFieldStandard').on('click', function () {
        $('#firstProductStandard [name="colorStandard[]"]').clone().appendTo('#testColorStandard');
        $('#firstProductStandard [name="sizeStandard[]"]').clone().appendTo('#testSizeStandard');
        $('#firstProductStandard .qtyProductStandard').clone().find('input').val('').end().appendTo('#testQtyStandard');
        // $('#firstProduct .testRemove').clone().appendTo('#testRemoveStandard');
    })

    $('.removeFieldStandard').on('click', function () {
        $('[name="colorStandard[]"]').last().remove();
        $('[name="sizeStandard[]"]').last().remove();
        $('[name="qtyStandard"]').last().remove();
        // $('.testRemoveStandard').last().remove();
    })
});




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