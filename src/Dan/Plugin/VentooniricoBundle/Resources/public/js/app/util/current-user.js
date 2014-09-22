define([
    'module',
    'app/models/user',
], function(module, User){
    var config = module.config();

    var CurrentUser = User.extend({
        url: config.url,
        isLogged: function() {
            return this.get('id');
        }
    });
    
    return new CurrentUser();
});
