const { exec } = require('child_process');

function runPythonScript() {
    exec('python update_assets.py', (error, stdout, stderr) => {
        const now = new Date().toLocaleString();
        if (error) {
            console.error(`[${now}] Hata:`, error.message);
            return;
        }
        if (stderr) {
            console.error(`[${now}] Hata çıktısı:`, stderr);
        }
        console.log(`[${now}] Çıktı:`, stdout);
    });
}

// Her dakika bir kez çalıştır
setInterval(runPythonScript, 60 * 1000);

// Başlangıçta da hemen çalıştır
runPythonScript(); 