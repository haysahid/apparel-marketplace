import Dexie from 'dexie';

export const db = new Dexie('MyFormDataDB');

// Mendefinisikan skema
// Simbol '++id' berarti ID akan auto-increment
db.version(1).stores({
    formData: 'id, content, updatedAt',
    selectedProduct: 'id, content, updatedAt',
});