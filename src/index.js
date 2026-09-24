export default {
  async fetch(request, env, ctx) {
    return new Response('Hello from P-harts Worker!');
  },
};