const express = require('express');
const crypto = require('crypto');
const request = require('supertest');
const webhookRouter = require('../src/routes/webhooks');

function appWithJson() {
  const app = express();
  app.use(express.json());
  app.use('/webhook', webhookRouter);
  return app;
}

describe('Webhook HMAC verification', () => {
  test('rejects invalid signature', async () => {
    const app = appWithJson();
    const res = await request(app).post('/webhook').set('X-Hub-Signature-256', 'sha256=bad').send({ ping: 'pong' });
    expect(res.status).toBe(401);
  });

  test('accepts valid signature', async () => {
    process.env.WEBHOOK_SECRET = 'testsecret';
    const app = appWithJson();
    const body = { ping: 'pong' };
    const hmac = crypto.createHmac('sha256', process.env.WEBHOOK_SECRET);
    const digest = 'sha256=' + hmac.update(JSON.stringify(body)).digest('hex');
    const res = await request(app).post('/webhook').set('X-Hub-Signature-256', digest).send(body);
    expect(res.status).toBe(200);
    expect(res.body.success).toBe(true);
  });
});
