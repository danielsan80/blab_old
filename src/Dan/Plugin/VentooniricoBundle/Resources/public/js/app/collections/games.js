define([
    'module',
    'backbone-loader',
    'app/models/game',
], function(module, Backbone, Game){
    var config = module.config();
    
    var GameCollection = Backbone.Collection.extend({
        url: config.url,
        model: Game
    });
    return GameCollection;
});
