import fs from 'fs';
import path from 'path';

const file = path.resolve('node_modules/@tailwindcss/node/dist/index.mjs');
if (fs.existsSync(file)) {
    let code = fs.readFileSync(file, 'utf8');
    if (!code.includes('replace(/\\0/g')) {
        code = code.replace(
            'return Oe(qr,e,r)',
            'let _r=await Oe(qr,e,r);return typeof _r===\'string\'?_r.replace(/\\0/g,\'\'):_r;'
        );
        fs.writeFileSync(file, code, 'utf8');
        console.log('[patch] Applied null byte fix for paths containing #');
    }
}
