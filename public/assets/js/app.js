

import Filter from './modules/Filter.js'

const filterController = new Filter(document.querySelector('.js-filter'));

// new Filter(document.querySelector('.js-details'));

const viewSelect = document.querySelector('[data-view-select]');
if (viewSelect) {
    const trigger = viewSelect.querySelector('[data-view-trigger]');
    const menu = viewSelect.querySelector('[data-view-menu]');
    const options = viewSelect.querySelectorAll('[data-view-option]');
    const valueDisplay = viewSelect.querySelector('[data-view-value]');
    const nativeSelect = viewSelect.querySelector('#maxItemPerPage');

    const applyViewLimit = (selectedValue) => {
        const params = new URLSearchParams(window.location.search);
        params.set('maxItemPerPage', selectedValue);
        params.set('page', '1');
        const query = params.toString();
        const nextUrl = window.location.pathname + (query ? '?' + query : '');

        if (filterController && typeof filterController.loadUrl === 'function') {
            filterController.loadUrl(nextUrl);
        } else {
            window.location.assign(nextUrl);
        }
    };

    const closeViewMenu = () => {
        viewSelect.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
    };

    trigger.addEventListener('click', (e) => {
        e.preventDefault();
        const isOpen = viewSelect.classList.toggle('is-open');
        trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    options.forEach((option) => {
        option.addEventListener('click', () => {
            const selectedValue = option.getAttribute('data-view-option');
            options.forEach((item) => item.classList.remove('is-selected'));
            option.classList.add('is-selected');
            valueDisplay.textContent = selectedValue;
            nativeSelect.value = selectedValue;
            applyViewLimit(selectedValue);
            closeViewMenu();
        });
    });

    document.addEventListener('click', (e) => {
        if (!viewSelect.contains(e.target)) {
            closeViewMenu();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeViewMenu();
        }
    });
}

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

// $(document).ready(function () {
//     $(".color2").hide();
//     /* when any radio element is clicked, Get the attribute value of that clicked radio element and show the radio_content div element which matches the attribute value and hide the remaining tab content div elements */
//     $(".colorChoice").click(function(){
//         var dataSend = $(this).attr("data-color");
//         // alert(current_raido);
//         $(".color").hide();
//         $(".color2").show();
//
//
//         // console.log(dataSend);
//         $.ajax({
//             url: '/shopShow',
//             type: "POST",
//             data: dataSend,
//             success: function (data) {
//                 // // if(data == dataSend) console.log("ok");
//                 console.log("OK");
//                 // alert('ok')
//             },
//             error: function (data) {
//                 alert("fail");
//             }
//         });
//
//     })
//
// });



$('#maxItemPerPage').change(function(){
    var item = $('#maxItemPerPage').find(":selected").val();
    var params = new URLSearchParams(window.location.search);
    params.set('maxItemPerPage', item);
    params.set('page', '1');
    var query = params.toString();
    var nextUrl = window.location.pathname + (query ? '?' + query : '');
    if (filterController && typeof filterController.loadUrl === 'function') {
        filterController.loadUrl(nextUrl);
    } else {
        window.location.assign(nextUrl);
    }
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
