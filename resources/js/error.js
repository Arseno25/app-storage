document.addEventListener('livewire:init', () => {
    Livewire.hook('request', ({uri, options, payload, respond, succeed, fail}) => {
        fail(({status, content, preventDefault}) => {
            if(status === 500)
            {
                new FilamentNotification()
                    .title('Something went wrong!')
                    .icon('heroicon-o-face-frown')
                    .danger()
                    .duration(10000)
                    .body('Sorry, an unexpected error occurred during your request. our engineer are fixing it.')
                    .send()

                preventDefault();
            }
        })
    });
});
