<?php 
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
}
$name = $_GET['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Supervisor</title>
    <link rel="stylesheet" href="assets/index.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div id="app" class="log">
        <div class="container">
            <h1>Supervisor <span><?php echo $name; ?> log</span></h1>
            <pre>{{log.data}}</pre>
            <div class="load" v-if="load"><el-button size="small" @click="loadMore">加载更多</el-button></div>
        </div>
    </div>
    <script src="assets/vue.min.js"></script>
    <script src="assets/index.js"></script>
    <script src="assets/axios.min.js"></script>
    <script>
        new Vue({
        el: '#app',
            data: function() {
                return {
                    log: {
                        data: ''
                    },
                    length: 1000,
                    start: 0,
                    load: false,
                    name: '<?php echo $name ?>'
                }
            },
            created: function () {
                var that = this;
                axios.post(this.api('readProcessStdoutLog'), {
                    name: that.name,
                    start: that.start,
                    length: that.length
                }).then(function (response) {
                    if (response.data.code !== 0) {
                        that.$message({
                            type: 'error',
                            message: response.data.message
                        });

                        return false;
                    }

                    that.log = response.data.data;
                    var length = response.data.data.length;

                    if (length === that.length) {
                        that.load = true;
                    }
                });
            },
            methods: {
                api: function (method) {
                    return '/develop/supervisor/api.php?method=' + method;
                },
                loadMore: function () {
                    var that = this;
                    that.start = (that.start * that.length) + 1;
                    axios.post(this.api('readProcessStdoutLog'), {
                        name: that.name,
                        start: that.start,
                        length: that.length
                    }).then(function (response) {
                        if (response.data.code !== 0) {
                            that.$message({
                                type: 'error',
                                message: response.data.message
                            });

                            return false;
                        }

                        var log = response.data.data;
                        that.log.data = that.log.data + log.data;
                        var length = response.data.data.length;

                        if (length === that.length) {
                            that.load = true;
                        }
                    });
                }
            }
        })
  </script>
</body>
</html>