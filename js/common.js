$(function () {
    $("#btnSendCode").click(function () {
        if ($("#Mobile").val() == "")
        {
            alert("手机号码不可为空");
            return false;
        }
        $.ajax({
            // 获取id，challenge，success（是否启用failback）
            url: "/Common/GetCaptcha",
            type: "post",
            dataType: "json", // 使用jsonp格式
            success: function (data) {
                // 使用initGeetest接口
                // 参数1：配置参数，与创建Geetest实例时接受的参数一致
                // 参数2：回调，回调的第一个参数验证码对象，之后可以使用它做appendTo之类的事件
                initGeetest({
                    gt: data.gt,
                    challenge: data.challenge,
                    product: "embed", // 产品形式
                    offline: !data.success,
                    width: 300
                }, handler);
            }
        });
    })
    $("#btnSendEmailCode").click(function () {
        if ($("#UserName").val() == "") {
            alert("邮件地址不可为空");
            return false;
        }
        $.ajax({
            // 获取id，challenge，success（是否启用failback）
            url: "/Common/GetCaptcha",
            type: "post",
            dataType: "json", // 使用jsonp格式
            success: function (data) {
                // 使用initGeetest接口
                // 参数1：配置参数，与创建Geetest实例时接受的参数一致
                // 参数2：回调，回调的第一个参数验证码对象，之后可以使用它做appendTo之类的事件
                initGeetest({
                    gt: data.gt,
                    challenge: data.challenge,
                    product: "embed", // 产品形式
                    offline: !data.success,
                    width: 300
                }, handleremail);
            }
        });
    })
})
var capojb;
function handler(captchaObj) {
    capojb = captchaObj;
    capojb.appendTo("#captcha");
    capojb.onSuccess(getsmscode);
    $(".geetest").css("display", "block");
}
function handleremail(captchaObj) {
    capojb = captchaObj;
    capojb.appendTo("#captcha");
    capojb.onSuccess(getemailcode);
    $(".geetest").css("display", "block");
}
function getsmscode() {
    var re = capojb.getValidate();
    $(".geetest").css("display", "none");
    $("#captcha").html("");
    var InterValObj; //timer变量，控制时间
    var count = 60; //间隔函数，1秒执行
    var curCount;//当前剩余秒数
    curCount = count;
    var phone = $("#Mobile").val();
    var data = { "phone": phone, "geetest_challenge": re.geetest_challenge, "geetest_validate": re.geetest_validate, "geetest_seccode": re.geetest_seccode, }
    if (phone != "") {
        if (!phone.match(/^(((1[3|4|5|7|8][0-9]{1}))+\d{8})$/)) {
            alert("手机号不正确");
            return false;
        }
        $.post('/Common/VerifyMobile/', data, function (result) {
            if (result.status == 0) {
                alert(result.message);
            } else {
                var time = 60;
                function timeCountDown() {
                    if (time == 0) {
                        clearInterval(timer);
                        $("#btnSendCode").removeAttr("disabled");//启用按钮
                        $("#btnSendCode").val("重新发送");
                        return true;
                    }
                    $('#btnSendCode').val(time + "秒后重试");
                    time--;
                    return false;
                }
                $("#btnSendCode").attr("disabled", "true");
                timeCountDown();
                var timer = setInterval(timeCountDown, 1000);
                alert(result.message);
            }
        }, "json"
            )
    } else {
        alert("手机号不能为空");
    }
}
