 :loop
     python update_assets.py
     timeout /t 60 >nul
     goto loop