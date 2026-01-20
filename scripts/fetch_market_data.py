import requests
import mysql.connector
from datetime import datetime
import os

# 【重要】Yahoo Finance用のシンボルのみを使用します
targets = {
    'USDJPY': 'JPY=X',
    'VIX': '^VIX',
    'US10Y': '^TNX',
    'DXY': 'DX-Y.NYB',
    'SP500': '^GSPC'
}

def fetch_price(symbol_id):
    # Yahoo Finance のAPIエンドポイント
    url = f"https://query1.finance.yahoo.com/v8/finance/chart/{symbol_id}"
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
    }

    try:
        response = requests.get(url, headers=headers, timeout=10)
        data = response.json()

        # JSONデータから価格を抽出
        price = data['chart']['result'][0]['meta']['regularMarketPrice']

        # 米10年債利回り(^TNX)は 4.123% が 41.23 のように送られてくるため調整
        if symbol_id == '^TNX':
            price = price / 10

        return float(price)
    except Exception as e:
        print(f"Error fetching {symbol_id}: {e}")
        return None

def save_to_db(symbol, price):
    if price is None:
        return

    try:
        conn = mysql.connector.connect(
            host=os.getenv('DB_HOST', 'mysql'),
            user=os.getenv('DB_USERNAME', 'sail'),
            password=os.getenv('DB_PASSWORD', 'password'),
            database='laravel'
        )
        cursor = conn.cursor()

        # market_dataテーブルへの挿入
        query = """
            INSERT INTO market_data
            (symbol, open, high, low, close, measured_at, created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
        """
        now = datetime.now()
        # すべての価格カラムに取得した価格を入れ、measured_atを現在時刻にする
        cursor.execute(query, (symbol, price, price, price, price, now, now, now))

        conn.commit()
        cursor.close()
        conn.close()
    except Exception as e:
        print(f"Database Error for {symbol}: {e}")

if __name__ == "__main__":
    for symbol, symbol_id in targets.items():
        price = fetch_price(symbol_id)
        if price:
            print(f"Fetched {symbol}: {price}")
            save_to_db(symbol, price)
        else:
            print(f"Failed to fetch {symbol}")