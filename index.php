<?php

session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
}

$path = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);

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
    <div id="app" class="index">
        <div class="container">
            <h1>Supervisor <span>{{info.supervisorVerison}}</span><a class="logout" href="login.php">退出</a></h1>

            <el-table :data="allProcess" border style="width: 100%">
                <el-table-column prop="statename" label="状态" width="120">
                    <template slot-scope="scope">
                        <el-tag v-if="scope.row.statename === 'RUNNING'" type="success">{{scope.row.statename.toLowerCase()}}</el-tag>
                        <el-tag v-else type="danger">{{scope.row.statename.toLowerCase()}}</el-tag>
                    </template>
                </el-table-column>
                <el-table-column prop="pid" label="PID" width="80"></el-table-column>
                <el-table-column prop="name" label="进程" width="180"></el-table-column>
                <el-table-column prop="statename" label="启动时间" width="130">
                    <template slot-scope="scope">{{timestampToTime(scope.row.start)}}</template>
                </el-table-column>
                <el-table-column prop="description" label="描述" width="180">
                </el-table-column>
                <el-table-column prop="statename" label="操作">
                    <template slot-scope="scope">
                        <el-button v-if="scope.row.statename === 'RUNNING'" size="small" @click="restart(scope.row.name)">重启</el-button>
                        <el-button v-if="scope.row.statename === 'STOPPED'" size="small" @click="startProcess(scope.row.name)">启动</el-button>
                        <el-button v-if="scope.row.statename === 'RUNNING'" size="small" @click="stop(scope.row.name)">停止</el-button>
                        <el-button size="small" @click="readProcessStdoutLog(scope.row.name)">查看日志</el-button>
                        <el-button size="small" @click="clearProcessLogs(scope.row.name)">清除日志</el-button>
                    </template>
                </el-table-column>
            </el-table>
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
                    log: '',
                    info: {},
                    allProcess: []
                }
            },
            created: function () {
                var that = this;

                // 读取所有的进程
                this.api('getAllProcessInfo', 'get', [], function (response) {
                    that.allProcess = response.data;
                });

                // 读取supervisor信息
                this.api('index', 'get', [], function (response) {
                    that.info = response.data;
                });
            },
            methods: {
                /**
                 * 拼接API
                 * @param string api
                 * @param string method
                 * @param object params
                 * @param callback success
                 * @param string message
                 */
                api: function (api, method, params = {}, success, message) {
                    var that = this;
                    var url = '<?php echo $path ?>api.php?method=' + api;
                    var result = {};

                    axios({
                        method: 'post',
                        url: url,
                        data: method === 'post' || method === 'put' ? params : null,
                        params: method === 'get' || method === 'delete' ? params : null,
                    }).then(function (response) {
                        if (response.data.code !== 0) {
                            that.$message({
                                type: 'error',
                                message: response.data.message
                            });
                            return false;
                        }
                        if (message) {
                            that.$message({
                                type: 'success',
                                message: message
                            });
                        }

                        if (success) {
                            success(response.data)
                        }
                    });
                },
                timestampToTime: function (timestamp) {
                    var date = new Date(timestamp * 1000)
                    var y = date.getFullYear() + '-'
                    var M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-'
                    var D = date.getDate() + ' '
                    var h = date.getHours() + ':'
                    var m = date.getMinutes()
                    return y + M + D + h + m
                },
                reset: function () {
                    var that = this;
                    // 读取所有的进程
                    this.api('getAllProcessInfo', 'get', {}, function (response) {
                        that.allProcess = response.data.data;
                    });
                },
                startProcess: function (name) {
                    var that = this;
                    this.api('startProcess', 'post', {
                        name: name
                    }, function (response) {
                        console.log(response);
                        that.reset();
                    }, '启动成功');
                },
                restartProcess: function (name) {
                    var that = this;
                    this.api('restartProcess', 'post', {
                        name: name
                    }, function () {
                        
                    }, '重启成功')
                },
                stopProcess: function (name) {
                    this.api('stopProcess', 'post', {
                        name: name
                    }, function (response) {
                        this.reset();
                    }, '进程已停止');
                },
                readProcessStdoutLog: function (name) {
                    location.href = 'log.php?name=' + name;
                },
                clearProcessLogs: function (name) {
                    this.api('clearProcessLogs', 'post', {
                        name: name
                    }, function () {}, '日志已经清空');
                }
            }
        })
  </script>
</body>
</html>