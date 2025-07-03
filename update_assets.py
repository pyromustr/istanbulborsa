from tvDatafeed import TvDatafeed, Interval
import mysql.connector
from datetime import datetime

def to_native(val):
    if hasattr(val, "item"):
        return val.item()
    return val

# --- Veritabanı Bağlantı Bilgileri ---
# Lütfen bu bilgileri kendi XAMPP MySQL kurulumunuza göre güncelleyin.
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '', # XAMPP varsayılan olarak boş parola kullanır
    'database': 'istanbulborsa'
}

FOREX_KEYWORDS = ["XAU", "USD", "EUR", "GBP", "NZD", "AUD", "CHF"]
CRYPTO_KEYWORDS = ["BTC", "ETH", "SOL", "ADA", "XRP"]

def update_assets_from_tvdatafeed():
    db_connection = None
    try:
        db_connection = mysql.connector.connect(**DB_CONFIG)
        cursor = db_connection.cursor()
        cursor.execute("SELECT id, asset_symbol FROM assets")
        db_assets = {symbol: asset_id for asset_id, symbol in cursor.fetchall()}
        if not db_assets:
            print("Veritabanında güncellenecek varlık bulunamadı.")
            return
        print(f"Veritabanında {len(db_assets)} adet varlık bulundu.")
        updated_count = 0

        tv = TvDatafeed()  # Girişsiz, istersen kullanıcı adı/şifre ekle

        for symbol, asset_id in db_assets.items():
            try:
                # Forex paritesi mi kontrol et
                is_forex = any(key in symbol for key in FOREX_KEYWORDS)
                is_crypto = any(key in symbol for key in CRYPTO_KEYWORDS)
                if is_crypto:
                
                    df = tv.get_hist(symbol=symbol, exchange='BINANCE', interval=Interval.in_daily, n_bars=2)
                
                elif is_forex:
                    # Forex için uygun exchange ve sembol formatı
                    # Örnek: EURUSD için symbol='EURUSD', exchange='OANDA'
                    df = tv.get_hist(symbol=symbol, exchange='OANDA', interval=Interval.in_daily, n_bars=2)
                
                else:
                    # BIST için
                    df = tv.get_hist(symbol=symbol, exchange='BIST', interval=Interval.in_daily, n_bars=2)
                if df is None or df.empty:
                    print(f"{symbol} için veri bulunamadı.")
                    continue

                last_bar = df.iloc[-1]
                prev_bar = df.iloc[-2] if len(df) > 1 else None

                current_price = last_bar['close']
                volume_24h = last_bar['volume']
                market_cap = last_bar.get('market_cap_basic', None)
                circulating_supply = last_bar.get('total_shares_outstanding', None)
                price_24h_ago = prev_bar['close'] if prev_bar is not None else None

                update_clauses = []
                update_values = []
                log_parts = []

                if current_price is not None:
                    update_clauses.append("current_price = %s")
                    update_values.append(to_native(current_price))
                    log_parts.append(f"Fiyat: {current_price:.2f}")

                if price_24h_ago is not None:
                    update_clauses.append("price_24h_ago = %s")
                    update_values.append(to_native(price_24h_ago))

                if volume_24h is not None:
                    update_clauses.append("volume_24h = %s")
                    update_values.append(to_native(volume_24h))
                    log_parts.append(f"Hacim: {volume_24h:.0f}")

                if market_cap is not None:
                    update_clauses.append("market_cap = %s")
                    update_values.append(to_native(market_cap))
                    log_parts.append(f"Piyasa Değeri: {market_cap:.0f}")

                if circulating_supply is not None:
                    update_clauses.append("circulating_supply = %s")
                    update_values.append(to_native(circulating_supply))

                # updated_at sütununu güncelle
                update_clauses.append("updated_at = %s")
                update_values.append(datetime.now())

                if update_clauses:
                    update_sql = f"UPDATE assets SET {', '.join(update_clauses)} WHERE id = %s"
                    update_values.append(asset_id)
                    cursor.execute(update_sql, tuple(update_values))
                    updated_count += 1
                    log_message = ", ".join(log_parts)
                    print(f"-> {symbol}: {log_message} -> GÜNCELLENDİ")

            except Exception as e:
                print(f"[HATA] {symbol} işlenirken hata: {e}")
                continue

        db_connection.commit()
        print(f"\nToplam {updated_count} adet varlık başarıyla güncellendi.")

    except mysql.connector.Error as err:
        print(f"Veritabanı hatası: {err}")
    except Exception as e:
        print(f"Genel hata: {e}")
    finally:
        if db_connection and db_connection.is_connected():
            cursor.close()
            db_connection.close()
            print("Veritabanı bağlantısı kapatıldı.")

if __name__ == '__main__':
    update_assets_from_tvdatafeed()

# Bu dosyanın her 1 dakikada bir otomatik çalışmasını istiyorsanız, aşağıdaki yöntemlerden birini kullanabilirsiniz:
# 
# 1. Windows Görev Zamanlayıcı (Task Scheduler) ile:
#    - Görev Zamanlayıcı'yı açın.
#    - "Temel Görev Oluştur" ile yeni bir görev ekleyin.
#    - Eylem olarak: Program/script kısmına python.exe'nin tam yolunu, "Argüman ekle" kısmına update_assets.py'nin tam yolunu yazın.
#    - Zamanlama olarak "Her 1 dakikada bir" seçin.
#
# 2. Alternatif olarak aşağıdaki .cmd dosyasını oluşturup çalıştırabilirsiniz:
#
#   :loop
#   python update_assets.py
#   timeout /t 60 >nul
#   goto loop
#
# Bu .cmd dosyasını arka planda çalıştırırsanız update_assets.py her dakika otomatik çalışır. 