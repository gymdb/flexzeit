<script>
  export default {
    data() {
      return {
        modal: false,
        isCourse: false,
        id: null,
        teacher: null,
        course: null,
        dates: null,
        lessons: null,
        chosen: [],
        error: null
      }
    },
    methods: {
      registerCourse(id, course, teacher, dates) {
        this.modal = true;
        this.isCourse = true;
        this.id = id;
        this.course = course;
        this.teacher = teacher;
        this.dates = dates;
      },
      registerLesson(teacher, lessons) {
        this.modal = true;
        this.isCourse = false;
        this.teacher = teacher;
        this.lessons = lessons;
        this.chosen = this.lessons.map(l => l.id);
      },
      cancel() {
        this.modal = false;
      },
      save() {
        function post(url) {
          this.$http.post(url).then(function (response) {
            if (response.data.success) {
              aggregate(true);
            } else {
              aggregate(false, response.data.error);
            }
          }).catch(function (error) {
            if (error.response) {
              aggregate(false, error.response.status);
            } else {
              aggregate(false, 100);
            }
          });
        }

        let responses = 0, count = this.chosen.length, aggSuccess = 0, errors = [];

        function aggregate(success, error) {
          if (success) {
            aggSuccess = 1;
          }
          if (error && errors.indexOf(error) < 0) {
            errors.push(error);
          }

          responses++;
          if (responses === count) {
            location.search = 'success=' + aggSuccess + (errors.length ? '&errors=' + errors : '');
          }
        }

        if (this.isCourse) {
          post.call(this, '/student/register/course/' + this.id);
        } else {
          this.chosen.forEach(id => post.call(this, '/student/register/lesson/' + id));
        }
      }
    }

  }
</script>
