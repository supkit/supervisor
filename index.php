<?php

session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
}

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
            <h1>Supervisor <span>{{info.supervisorVerison}}</span></h1>

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
                        <el-button v-if="scope.row.statename === 'STOPPED'" size="small" @click="start(scope.row.name)">启动</el-button>
                        <el-button v-if="scope.row.statename === 'RUNNING'" size="small" @click="stop(scope.row.name)">停止</el-button>
                        <el-button size="small" @click="readLog(scope.row.name)">查看日志</el-button>
                        <el-button size="small" @click="clearLog(scope.row.name)">清除日志</el-button>
                    </template>
                </el-table-column>
            </el-table>

            <el-dialog title="查看日志" :visible.sync="dialogReadLogVisible" width="60%">
                <pre>{{log}}</pre>
            </el-dialog>
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
                    dialogReadLogVisible: false,
                    log: '',
                    info: {},
                    allProcess: []
                }
            },
            created: function () {
                var that = this;

                // 读取所有的进程
                axios.get('/develop/supervisor/api.php?method=getAllProcessInfo').then(function (response) {
                    that.allProcess = response.data.data;
                });

                axios.get('/develop/supervisor/api.php?method=index').then(function (response) {
                    console.log(response);
                    that.info = response.data.data;
                });
            },
            methods: {
                api: function (method) {
                    return '/develop/supervisor/api.php?method=' + method;
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
                    axios.get('/develop/supervisor/api.php?method=getAllProcessInfo').then(function (response) {
                        that.allProcess = response.data.data;
                    });
                },
                start: function (name) {
                    var that = this;
                    axios.post(this.api('start'), {
                        name: name
                    }).then(function (response) {
                        if (response.data.code !== 0) {
                            that.$message({
                                type: 'error',
                                message: response.data.message
                            });

                            return false;
                        }
                        that.$message({
                            type: 'success',
                            message: '重启成功'
                        });

                        // 重置
                        that.reset();
                    });
                },
                restart: function (name) {
                    var that = this;
                    axios.post(this.api('restart'), {
                        name: name
                    }).then(function (response) {
                        if (response.data.code !== 0) {
                            that.$message({
                                type: 'error',
                                message: response.data.message
                            });

                            return false;
                        }
                        that.$message({
                            type: 'success',
                            message: '重启成功'
                        });

                        // 重置
                        that.reset();
                    });
                },
                stop: function (name) {
                    var that = this;
                    axios.post(this.api('stop'), {
                        name: name
                    }).then(function (response) {
                        if (response.data.code !== 0) {
                            that.$message({
                                type: 'error',
                                message: response.data.message
                            });

                            return false;
                        }
                        that.$message({
                            type: 'success',
                            message: '进程已停止'
                        });

                        // 重置
                        that.reset();
                    });

                    // 读取所有的进程
                    axios.get('/develop/supervisor/api.php?method=getAllProcessInfo').then(function (response) {
                        console.log(response);
                        that.allProcess = response.data.data;
                    });
                },
                readLog: function (name) {
                    location.href = 'log.php?name='+name;
                    return true;
                    var that = this;
                    axios.post(this.api('readProcessStdoutLog'), {
                        name: name
                    }).then(function (response) {
                        if (response.data.code !== 0) {
                            that.$message({
                                type: 'error',
                                message: response.data.message
                            });

                            return false;
                        }

                        that.log = response.data.data;
                        that.dialogReadLogVisible = true;
                    });
                },
                clearLog: function (name) {
                    var that = this;
                    axios.post(this.api('clearProcessLogs'), {
                        name: name
                    }).then(function (response) {
                        if (response.data.code !== 0) {
                            that.$message({
                                type: 'error',
                                message: response.data.message
                            });

                            return false;
                        }
                        that.$message({
                            type: 'success',
                            message: '日志已经清空'
                        });
                    });
                }
            }
        })
  </script>
</body>
</html>