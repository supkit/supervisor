<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login</title>
    <link rel="stylesheet" href="assets/index.css">
    <style>
        html, body {
            height: 100%;
        }
        #app {
            height: 100%;
        }
        .login {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            background-color: #F7FCFD;
            width: 100%;
            min-height: 100%;
            background-repeat: no-repeat;
            background-position: center;
            background-size: 100%;
        }
        .login .container {
            width: 270px;
            background-color: #fff;
            padding: 30px 40px 30px 40px;
            border-radius: 3px;
            margin-bottom: 20px;
            border: 1px solid #EEF0F1;
        }
        .login .container .logo {
            text-align: center;
            padding-bottom: 20px;
        }
        h1 {
            color: #C50807;
            font-size: 34px;
            margin-bottom: 20px;
            font-family: Palatino, Optima, Georgia, serif, "Hiragino Sans GB", "Microsoft YaHei", "STHeiti", "SimSun", "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", 'Segoe UI', AppleSDGothicNeo-Medium, 'Malgun Gothic', Verdana, Tahoma, sans-serif;
        }
    </style>
</head>
<body>

<div id="app" class="login">
    <div class="container">
      <div class="logo"><h1>Supervisor</h1></div>
      <el-form :model="input" status-icon :rules="rules" ref="input">
        <el-form-item prop="account">
          <el-input
            placeholder="请输入账号" v-model="input.account">
          </el-input>
        </el-form-item>
        <el-form-item prop="password">
          <el-input
            placeholder="请输入密码" type="password" v-model="input.password">
          </el-input>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" native-type="submit" @click.native.prevent="submit('input')" :loading="loading" style="width: 100%;">登 录</el-button>
        </el-form-item>
      </el-form>
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
                    loading: false,
                    input: {
                        account: '',
                        password: ''
                    },
                    rules: {
                        account: [
                            {required: true, message: '请输入账号', trigger: 'blur'}
                        ],
                        password: [
                            {required: true, message: '请输入密码', trigger: 'blur'}
                        ]
                    }
                }
            },
            created: function () {
                
            },
            methods: {
                api: function (method) {
                    return '/develop/supervisor/api.php?method=' + method;
                },
                submit: function (formName) {
                    this.$refs[formName].validate((valid) => {
                        if (valid) {
                            return this.request()
                        }
                        console.log('error submit')
                        return false
                    })
                },
                request: function () {
                    var that = this;
                    axios.post(this.api('login'), this.input).then(function (response) {
                        if (response.data.code !== 0) {
                            that.$message({
                                type: 'error',
                                message: response.data.message
                            });

                            return false;
                        }
                        location.href = 'index.php';
                    });
                }
            }
        })
    </script>
</body>
</html>