define([
    'module',
    'backbone-loader',
    'app/models/user',
    'app/models/desire',
], function(module,Backbone, User, Desire){
     var Join = Backbone.RelationalModel.extend({
        urlRoot: module.config().urlRoot,
        relations: [
            {
                type: Backbone.HasOne,
                key: 'user',
                relatedModel: User
            },
            {
                type: Backbone.HasOne,
                key: 'desire',
                relatedModel: Desire
            }
        ]
    });
    
    return Join;
});
