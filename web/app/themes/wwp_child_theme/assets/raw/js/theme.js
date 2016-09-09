(function($,ns){

    var themeJs = {

        //Chef d'orchestre, que veux faire mon theme?
        init: function(){
            var t = this;

            $(document).one('ready', function() {
                t.$wrap = $('body');

                //appeler ici une fonction par fonctionnalite
                //Ex: this.registerMenuToggle()
                t.initGlobalComponents();
                t.runCurrentPageJs();
            });
        },

        initGlobalComponents: function(){
          var registeredComponents = ns.app.getComponents();
            if(registeredComponents){ for(var i in registeredComponents){
                var thisRegisteredComp = registeredComponents[i];
                if(thisRegisteredComp && thisRegisteredComp.opts && thisRegisteredComp.opts.initGlobal){
                    new thisRegisteredComp.component();
                }
            }}
        },

        runCurrentPageJs: function(){
            var $pageWrap = this.$wrap.find('#content > article.page');
            //does the page give instructions about the class to load?
            var potentialClassName = $pageWrap.data('name') ? 'Page'+$pageWrap.data('name') : 'Page';
            var potentialClass = ns[potentialClassName];

            //If yes, load it, otherwise, load ns.Page. This potential class should inherit from ns.Page
            var classtoCall = (typeof potentialClass == 'function') ? potentialClass : ns['Page'];

            //Init page class
            var p = new classtoCall($pageWrap).init();
            return this;
        }

    };

        themeJs.init();


})(jQuery,window.wonderwp);