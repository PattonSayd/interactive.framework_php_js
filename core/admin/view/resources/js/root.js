
var Root = function () {

    var _alertSetIntervat = function() {
        $('.gn-alert').addClass("gn-show");
        $('.gn-alert').removeClass("gn-hide");
        $('.gn-alert').addClass("gn-alert-show");
        $('.gn-alert').attr("tabindex",-1).focus()
        // setTimeout(function(){
        //     $('.gn-alert').removeClass("gn-show");
        //     $('.gn-alert').addClass("gn-hide");
        // },3000)
        
        $('.gn-alert').blur(function(){
            $('.gn-close').removeClass("gn-show")
            $('.gn-alert').addClass("gn-hide");
            
        });
        

        
    };

    var _alertClose = function() { 
               
        $('.gn-close').click(function(){
            $('.gn-alert').removeClass("gn-show");
            $('.gn-alert').addClass("gn-hide");
        });
        
    };


    return {

        // Enable transitions when page is fully loaded
        initAfterLoad: function() {
            _alertSetIntervat();
            _alertClose();
        },
    }
}();

window.addEventListener('load', function() {
    Root.initAfterLoad();
});
;
            