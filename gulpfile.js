const elixir = require('laravel-elixir');
const gulp  = require('gulp');

//this will build all dist from ./semantic/src/ and place it in public/assets/
const build = require('./semantic/tasks/build');
const Task = elixir.Task;
gulp.task('build', 'Builds all files from source',build);
elixir.extend('semanticBuild', () => {
     new Task('semanticBuild', () => {
         gulp.start('build');
     });
});

//run all essential tasks
elixir(mix => {
    mix.copy('./node_modules/toastr/build/toastr.css','./resources/assets/sass/toastr.css')
        .sass(['toastr.css','app.scss'],'./public/css/app.css')
       .sass('auth.scss')
       .webpack('app.js');
       //.semanticBuild();
});
