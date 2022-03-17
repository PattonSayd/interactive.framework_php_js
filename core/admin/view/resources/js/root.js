
var Root = function () {

    var _alertSetIntervat = function() {
        $('.gn-alert').addClass("gn-show");
        $('.gn-alert').removeClass("gn-hide");
        $('.gn-alert').addClass("gn-alert-show");
        setTimeout(function(){
            $('.gn-alert').removeClass("gn-show");
            $('.gn-alert').addClass("gn-hide");
        },3000)

        
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
            