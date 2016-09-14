var NutshellModx = function(config) {
    config = config || {};
NutshellModx.superclass.constructor.call(this,config);
};
Ext.extend(NutshellModx,Ext.Component,{
    page:{},window:{},grid:{},tree:{},panel:{},combo:{},config: {}
});
Ext.reg('nutshellmodx',NutshellModx);
NutshellModx = new NutshellModx();