const WhatsAppService = require('../src/services/WhatsAppService');

describe('WhatsAppService.formatPhoneNumber', () => {
  const svc = new WhatsAppService({ emit: () => {} });

  test('agrega 58 si número tiene 10 dígitos', () => {
    const jid = svc.formatPhoneNumber('4121234567');
    expect(jid).toBe('584121234567@s.whatsapp.net');
  });

  test('no duplica 58 si ya está presente', () => {
    const jid = svc.formatPhoneNumber('584121234567');
    expect(jid).toBe('584121234567@s.whatsapp.net');
  });
  
  test('usa countryCode proporcionado y quita 0 después del código', () => {
    const jid = svc.formatPhoneNumber('04121234567', { countryCode: '57' });
    expect(jid).toBe('574121234567@s.whatsapp.net');
  });
});
