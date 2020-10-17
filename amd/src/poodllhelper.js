define(["jquery", "core/log"], function ($, log) {
    "use strict"; // jshint ;_;

    log.debug("poodll helper: initialising");

    return {


        init: function (opts) {
            var prquestion = function (evt) {
                if(evt && evt[1]=='filesubmitted'){
                    // post a custom event that a filter template might be interested in
                    var prquestionUploaded = new CustomEvent("prquestionUploaded", {details: evt});
                    document.dispatchEvent(prquestionUploaded);
                }
            }; //end of callback function
            window.prquestion = prquestion;

        } //end of cp init

    };//end of return object
});