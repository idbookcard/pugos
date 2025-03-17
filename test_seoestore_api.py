import requests

# API 端点
API_URL = "https://panel.seoestore.net/action/api.php"

# 你的 API Key 和邮箱
API_KEY = "18c42d9cb1647194ec20751309b290b7"
EMAIL = "guozecan@gmail.com"

# 发送的数据
data = {
    "api_key": API_KEY,  # 必须包含
    "email": EMAIL,      # 必须包含
    "action": "services"  # API 操作（查询余额）
}

def call_api():
    try:
        # 发送 POST 请求
        response = requests.post(API_URL, data=data, headers={"User-Agent": "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"})
        
        # 解析 API 返回的数据
        if response.status_code == 200:
            print("✅ API 请求成功！")
            print("🔄 返回数据：", response.json())  # 解析 JSON
        else:
            print(f"❌ 请求失败，状态码：{response.status_code}")
            print("⚠️ 错误信息：", response.text)
    
    except Exception as e:
        print("❌ 请求失败，错误详情：", str(e))

# 运行测试
if __name__ == "__main__":
    call_api()
