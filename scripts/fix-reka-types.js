import fs from 'node:fs';
import path from 'node:path';

const distDir = path.resolve('node_modules/reka-ui/dist');

if (fs.existsSync(distDir)) {
    const files = fs.readdirSync(distDir);
    for (const file of files) {
        if (file.endsWith('.d.cts')) {
            const target = path.join(distDir, file.replace(/\.d\.cts$/, '.d.ts'));
            if (!fs.existsSync(target)) {
                fs.copyFileSync(path.join(distDir, file), target);
            }
        }
    }
}

