define([
    'module',
    'backbone-loader',
    'app/models/desire',
], function(module, Backbone, Desire){
    var config = module.config();
    
    var DesireCollection = Backbone.Collection.extend({
        url: config.url,
        model: Desire
    });
    return DesireCollection;
});
