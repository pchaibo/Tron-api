package main

import (
	"bytes"
	"fmt"
	"io"
	"log"
	"os"

	"gopkg.in/ini.v1"
)

var logs *log.Logger

func init() {
	writer1 := &bytes.Buffer{}
	writer2 := os.Stdout
	logfile, _ := os.OpenFile("log.txt", os.O_WRONLY|os.O_CREATE|os.O_APPEND, 0644)
	logs = log.New(io.MultiWriter(writer1, writer2, logfile), "log: ", log.Ldate|log.Ltime)
	logs.Println("start ")

}

func main() {
	// res, _ := exec.Command("python", "-V").Output()
	// fmt.Println(string(res))
	//Commtest()
	Trc20canBlock() //扫块
	//Start()
}

func Start() {
	conf, err := ini.Load("./conf/app.conf")
	if err != nil {
		fmt.Print(err)
	}
	ip := conf.Section("server").Key("ip").String()
	runmode := conf.Section("server").Key("runmode").String()
	s := routers.Routerinit(runmode)
	s.Run(ip)
}

// 测试
func Commtest() {
	common.Getcpu()
	//common.Getcpuload()
}
