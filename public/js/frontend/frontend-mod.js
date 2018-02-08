$(document).ready(function(){
    'use strict';
    $('#table-stack-1').cardtable();
    $(function(){
        $('.navbar .navbar-collapse').on('show.bs.collapse', function(e) {
            $('.navbar .navbar-collapse').not(this).collapse('hide');
            console.log('hejo');
        });
    });
    $('body').on('click','.option li',function(){
        var i = $(this).parents('.select').attr('id');
        var v = $(this).children().text();
        var o = $(this).attr('id');
        $('.selected').attr('id',o);
        $('.selected').text(v);
    });

        $('#sm1').click(function (){
            console.log('hello');
            $.ajax({
                url: "https://api.myjson.com/bins/199v1p",
                dataType: 'json',
                success: function() {
                    console.log('jsonLoaded');
                }
            });

        }
    );
});