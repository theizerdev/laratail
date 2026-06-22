const antiBlock = require('../src/middleware/antiBlockProtection');

describe('AntiBlockProtection.validateRecipient', () => {
  test('acepta JID válido de usuario', () => {
    expect(() => antiBlock.validateRecipient('584241703465@s.whatsapp.net')).not.toThrow();
  });

  test('acepta JID válido de grupo', () => {
    expect(() => antiBlock.validateRecipient('123456789012345@g.us')).not.toThrow();
  });

  test('rechaza número sin sufijo JID', () => {
    expect(() => antiBlock.validateRecipient('584241703465')).toThrow('Formato de número WhatsApp inválido');
  });
});

describe('AntiBlockProtection.validateMessageContent', () => {
  test('rechaza mensaje demasiado corto', () => {
    expect(() => antiBlock.validateMessageContent('a')).toThrow('Mensaje demasiado corto');
  });

  test('acepta mensaje normal', () => {
    expect(() => antiBlock.validateMessageContent('Hola mundo')).not.toThrow();
  });
});
