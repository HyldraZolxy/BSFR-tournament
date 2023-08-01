const gulp      = require("gulp");
const concatCss = require("gulp-concat-css");

gulp.task("default", () => {
    return gulp.src("www/css/*.min.css")
        .pipe(concatCss("bundle.css"))
        .pipe(gulp.dest("www/css/"));
});