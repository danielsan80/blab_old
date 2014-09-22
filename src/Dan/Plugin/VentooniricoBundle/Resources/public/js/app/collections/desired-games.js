define([
    'module',
    'backbone-loader',
    'app/models/game',
], function(module, Backbone, Game){
    
    var DesiredGameCollection = Backbone.Collection.extend({
        url: module.config().url,
        model: Game
    });
    return DesiredGameCollection;
});
