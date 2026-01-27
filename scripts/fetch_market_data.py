import requests
import mysql.connector
import pandas as pd
from datetime import datetime
import os

# Yahoo Finance用のシンボル
targets = {
    'USDJPY': 'JPY=X',
    'VIX': '^VIX',
    'US10Y': '^TNX',
    'DXY': 'DX-Y.NYB',
    'SP500': '^GSPC'
}

def compute_rsi(data, window):
    """RSIを計算する補助関数（この場所に出すことでエラーを防ぎます）"""
    delta = data.diff()
    gain = (delta.where(delta > 0, 0)).rolling(window=window).mean()
    loss = (-delta.where(delta < 0, 0)).rolling(window=window).mean()
    rs = gain / loss
    return 100 - (100 / (1 + rs))

def calculate_indicators(df):
    """
    テクニカル指標の計算
    """
    # 移動平均線の計算 (25, 75, 200)
    df['ma25'] = df['close'].rolling(window=25).mean()
    df['ma75'] = df['close'].rolling(window=75).mean()
    df['ma200'] = df['close'].rolling(window=200).mean()

    # RSI (14) の計算
    df['rsi'] = compute_rsi(df['close'], 14)

    return df

def fetch_history_and_calculate(symbol_id):
    url = f"https://query1.finance.yahoo.com/v8/finance/chart/{symbol_id}?range=300d&interval=1d"
    headers = {'User-Agent': 'Mozilla/5.0'}

    try:
        response = requests.get(url, headers=headers, timeout=10)
        data = response.json()
        result = data['chart']['result'][0]

        timestamps = result['timestamp']
        closes = result['indicators']['quote'][0]['close']

        # DataFrame作成
        df = pd.DataFrame({'close': closes}, index=pd.to_datetime(timestamps, unit='s'))

        # 指標計算
        df = calculate_indicators(df)

        # 最新の1行を取得
        latest = df.iloc[-1]
        price = float(latest['close'])

        # 米10年債利回り調整
        if symbol_id == '^TNX':
            price = price / 10
            latest['ma25'] = latest['ma25'] / 10 if pd.notnull(latest['ma25']) else None
            latest['ma75'] = latest['ma75'] / 10 if pd.notnull(latest['ma75']) else None
            latest['ma200'] = latest['ma200'] / 10 if pd.notnull(latest['ma200']) else None

        return {
            'price': price,
            'ma25': float(latest['ma25']) if pd.notnull(latest['ma25']) else None,
            'ma75': float(latest['ma75']) if pd.notnull(latest['ma75']) else None,
            'ma200': float(latest['ma200']) if pd.notnull(latest['ma200']) else None,
            'rsi': float(latest['rsi']) if pd.notnull(latest['rsi']) else None
        }
    except Exception as e:
        print(f"Error fetching {symbol_id}: {e}")
        return None

def save_to_db(symbol, data):
    if data is None: return
    try:
        conn = mysql.connector.connect(
            host='mysql',
            user='sail',
            password='password',
            database='laravel'
        )
        cursor = conn.cursor()
        query = """
            INSERT INTO market_data
            (symbol, open, high, low, close, ma25, ma75, ma200, rsi, measured_at, created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        now = datetime.now()
        p = data['price']
        cursor.execute(query, (
            symbol, p, p, p, p,
            data['ma25'], data['ma75'], data['ma200'], data['rsi'],
            now, now, now
        ))
        conn.commit()
        cursor.close()
        conn.close()
    except Exception as e:
        print(f"Database Error for {symbol}: {e}")

if __name__ == "__main__":
    for symbol, symbol_id in targets.items():
        result = fetch_history_and_calculate(symbol_id)
        if result:
            rsi_val = f"{result['rsi']:.2f}" if result['rsi'] is not None else "N/A"
            print(f"Fetched {symbol}: {result['price']} (RSI: {rsi_val})")
            save_to_db(symbol, result)