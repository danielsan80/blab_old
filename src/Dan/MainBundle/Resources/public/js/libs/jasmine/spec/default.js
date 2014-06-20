define(["app/models/user", "jasmine-html"], function(User) {
    describe("User", function() {
        it("should have a name", function() {
            user = new User();
            console.log(user);
            expect(user.get('name')).toEqual('danilo');

        });

    });
    
    return true;
});
