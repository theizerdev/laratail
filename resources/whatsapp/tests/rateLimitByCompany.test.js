const express = require('express');
const request = require('supertest');
const rateLimitByCompany = require('../src/middleware/rateLimitByCompany');

function createAppWithCompany(company) {
  const app = express();
  app.use((req, res, next) => { req.company = company; next(); });
  app.post('/send', rateLimitByCompany, (req, res) => res.json({ ok: true }));
  return app;
}

describe('rateLimitByCompany middleware', () => {
  test('aplica límite por empresa', async () => {
    const app = createAppWithCompany({ id: 1, rateLimitPerMinute: 1 });
    const res1 = await request(app).post('/send').send({});
    expect(res1.status).toBe(200);
    const res2 = await request(app).post('/send').send({});
    expect(res2.status).toBe(429);
  });
});
