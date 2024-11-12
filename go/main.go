package main

import (
	"bytes"
	"io"
	"log"
	"os"
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
