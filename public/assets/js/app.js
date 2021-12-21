

import Filter from './modules/Filter.js'

new Filter(document.querySelector('.js-filter'));

const slider = document.getElementById('slider');

if (slider){

    const min = document.getElementById('min');
    const max = document.getElementById('max');
    const minValue = Math.floor(parseInt(slider.dataset.min, 10 ) / 10 ) * 10;
    const maxValue = Math.ceil(parseInt(slider.dataset.max, 10 ) / 10 ) * 10;

    const range = noUiSlider.create(slider, {
        start: [min.value || minValue , max.value || maxValue],
        connect: true,
        step: 10,
        range: {
            'min': minValue,
            'max': maxValue
        }
    })

    range.on('slide', function (values, handle){
        if (handle === 0 ) {
            min.value = Math.round(values[0])
        }
        if (handle === 1 ) {
            max.value = Math.round(values[1])
        }
        console.log(values, handle)
    })

    range.on('end', function (values, handle) {
        if (handle===0) {
            min.dispatchEvent(new Event('change'))
        } else {
            max.dispatchEvent(new Event('change'))
        }
    })
}

////////////////////////////////////////////
// $(document).ready(function (){
//     $('.maxItemPerPage').on('change', function (){
//         alert('h')
//     })
// });

// $(document).ready(function (){
//     var Id = $(this).attr("data-userId");
//     // alert(Id)
//     $('.editCheckoutBtn').on('click', function (){
//
//         var Url = "/registeredit"+Id
//         $('#formEdit').attr("action",Url);
//
//     })
// });

// $(document).ready(function() {
// $("#myButtonPayment").click(function() {
//     e.preventDefault();
//     $("#myFormCheckout").submit();
// });
// });form_success

$(document).ready(function() {
    $("#form_success").hide();
    $("#myFormCheckout").submit(function(e) {
        e.preventDefault();
        $(".btn-disabled").toggleClass("transparent");

        $("#first").hide();
        $("#form_success").show();
        window.scrollTo({
            top: 350,
            behavior: 'smooth'
        })
        // $("#second").show();

    });
});


$('#maxItemPerPage').change(function(){

//     // alert($('#maxItemPerPage').find("option:selected").text());
    var url = "{{path(app.request.attributes.get('_route'),{'maxItemPerPage': '_itemNum'})}}";
    var item = $('#maxItemPerPage').find(":selected").text();
    jQuery(location).attr('href', url.replace('_itemNum',item ));
})





// let a = 'hey';
// let b = { name:'john' };
//
// func();
//
// console.log(`${a} ${b.name}`)
//
// function func(a, b){
//     a ='hello';
//     b.name ='me';
// }

// on select change, you navigate to indexAction and send the parameter maxItemPerPage
// $('#maxItemPerPage').change(function(){
//     // alert($('#maxItemPerPage').find("option:selected").text());
//     // var url = '{{path(\'app_product_shop\',\'maxItemPerPage\':_itemNum)}}';
//     // var item = $('#maxItemPerPage').find(":selected").text();
//     // var item = $('select[name="maxItemPerPage"] option:selected').text();
//     var item = $("select[id=maxItemPerPage] option:selected").text()
//     //
//     // // window.location.href = url.replace('_itemNum',item );
//     alert(item)
// })

// $("#maxItemPerPage").change(function() {
//     console.log($("option:selected", this).text()); //text
//     console.log($(this).val()); //value
// })