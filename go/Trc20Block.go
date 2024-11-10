package main

import (
	"crypto/sha256"
	"encoding/hex"
	"encoding/json"
	"fmt"
	"io"
	"log"
	"net/http"
	"net/url"
	"os"
	"strconv"
	"strings"
	"time"

	"github.com/mr-tron/base58"

	gjson "github.com/tidwall/gjson"
)

const (
	apiURL          = "https://api.trongrid.io"
	contractAddress = "TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t" // 替换为你要监控的 TRC20 合约地址

)

type BlockResponse struct {
	BlockNumber int64  `json:"block_header"`
	BlockHash   string `json:"blockID"`
}

type Date struct {
	Amount           int64  `json:"amount"`
	Owner_address    string `json:"owner_address"`
	Contract_address string `json:"contract_address"`
	Data             string `json:"data"`
}

// 41 转换base58adddrss
func Gettronhex(str string) (to string) {
	addb, _ := hex.DecodeString(str)
	firstHash := sha256.Sum256(addb)
	secondHash := sha256.Sum256(firstHash[:])
	secret := secondHash[:4]
	addb = append(addb, secret...)
	to = base58.Encode(addb)
	return
}

func Getdata(geturl string) (Doby []byte, err error) {

	purl, _ := url.Parse("http://127.0.0.1:1080")
	client := http.Client{
		Transport: &http.Transport{Proxy: http.ProxyURL(purl)},
	}
	resp, _ := http.NewRequest("GET", geturl, nil)
	resp.Header.Set("TRON-PRO-API-KEY", "5f228046-7d8d-472c-a7b0-ce1a8402cfee")
	latestBlockResponse, err := client.Do(resp)
	//latestBlockResponse, err := client.Get(geturl)
	if err != nil {
		fmt.Println("Error:", err)
		return
	}
	defer latestBlockResponse.Body.Close()
	bodys, err := io.ReadAll(latestBlockResponse.Body)
	Doby = bodys
	if err != nil {
		fmt.Println("Error:", err, bodys)
		return
	}
	//fmt.Println(string(bodys))
	return

}

func Trc20canBlock() {

	logfile, _ := os.OpenFile("log.txt", os.O_CREATE|os.O_WRONLY|os.O_APPEND, 0666)
	log.SetOutput(logfile)
	log.Println("start")

	// 获取最新区块号
	//latestBlockURL := fmt.Sprintf("%s/walletsolidity/getnowblock", apiURL)
	latestBlockURL := fmt.Sprintf("%s/wallet/getnowblock", apiURL)
	body, err := Getdata(latestBlockURL)
	if err != nil {
		log.Println("url:", latestBlockURL)
		fmt.Println("Error:", err)
		return
	}

	var latestBlockData map[string]interface{}
	err = json.Unmarshal(body, &latestBlockData)
	if err != nil {
		log.Println("json:", err.Error())
		fmt.Println("jsoError:", err)
		return
	}
	latestBlockNumber := int(latestBlockData["block_header"].(map[string]interface{})["raw_data"].(map[string]interface{})["number"].(float64))
	// 监控的起始区块号
	//startBlock := latestBlockNumber - numberOfBlocks
	startBlock := latestBlockNumber
	fmt.Println("newid:", latestBlockNumber)

	// 获取每个区块的交易记录
	for startBlock > 0 {
		blockURL := fmt.Sprintf("%s/wallet/getblockbynum?num=%d", apiURL, startBlock)
		go Setdata(blockURL, logfile)
		startBlock++
		time.Sleep(3 * time.Second)
	}

}

func Setdata(blockURL string, logfile *os.File) {
	log.SetOutput(logfile)
	blockBody, err := Getdata(blockURL)
	if err != nil {
		log.Println("eorr:", blockURL)
		fmt.Println("Error:", err)
		return
	}

	var block BlockResponse
	num := gjson.Get(string(blockBody), "block_header.raw_data.number").Int()
	block.BlockNumber = num

	BlockHash := gjson.Get(string(blockBody), "block_header.raw_data.parentHash").String()
	block.BlockHash = BlockHash
	transactions := gjson.Get(string(blockBody), "transactions")
	//fmt.Println("transactions:", transactions)
	// 输出每个区块的交易记录
	fmt.Println("BlockNumber:", block.BlockNumber)
	//fmt.Println("Block Hash:", block.BlockHash)
	// 交易处理
	transactions.ForEach(func(key, value gjson.Result) bool {
		succ := value.Get("ret.#.contractRet").Array()
		if succ[0].String() == "SUCCESS" {
			conitype := value.Get("raw_data.contract.#.type").Array()
			types := conitype[0].String()
			// TriggerSmartContract 合约
			if types == "TriggerSmartContract" {
				value1 := value.Get("raw_data.contract.#.parameter.value").Array()
				values := value1[0].String()
				var datas Date
				json.Unmarshal([]byte(values), &datas)

				contract_addressx := value.Get("raw_data.contract.#.parameter.value.contract_address").Array()
				contract_address := contract_addressx[0].String()
				//usdt
				if strings.HasPrefix(datas.Data, "a9059cbb") && Gettronhex(contract_address) == contractAddress {

					owner_addressx := value.Get("raw_data.contract.#.parameter.value.owner_address").Array()
					owner_address := owner_addressx[0].String()

					input := datas.Data
					toaddress := input[30:72]
					if toaddress[0:1] == "0" {
						toaddress = "41" + toaddress[2:]
					}
					amountx := input[72:]
					amount, merr := strconv.ParseInt(amountx, 16, 64)
					amountint := amount / 1000000
					if err != nil {
						fmt.Println("amounterr", merr, amount)
					}
					var amountk float64
					if amountint < 10 {
						amountk = float64(amount/10000) * 0.01
					} else {
						amountk = float64(amountint)
					}
					// txID := value.Get("txID").String()
					// fmt.Println("txID:", txID)
					// if amountint > 500000 {
					// 	fmt.Println("data:", value.String())
					// }
					//fmt.Println(amountk)
					fmt.Printf("num: %d from: %s   toaddress: %s amount: %.2f \n", block.BlockNumber, Gettronhex(owner_address), Gettronhex(toaddress), amountk)
					time.Sleep(100 * time.Millisecond)
				}

			}

		}

		return true
	})
}
