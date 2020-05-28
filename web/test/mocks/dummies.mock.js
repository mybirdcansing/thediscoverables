
    export const dummySong = function (id) {
        return { 
            id: id,
            title: 'dummy song',
            filename: 'dummy_song.mp3',
            description: 'a dummy song ',
            fileInput: null,
            duration: 199.99
        };
    };

    export const dummyUser = function (id) {
        return { 
            id: id,
            username: "dummyusername",
            firstName: "Dummy",
            lastName: "User",
            email: "dummy.user@gmail.com",
            password: 'pass',
            statusId: "1"
        };
    }