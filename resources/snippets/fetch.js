const request = { url: 'https://duckduckgo.com' };
const response = await fetch(':endpoint', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(request),
});
const html = await response.text();

console.log(`Website HTML: ${html}`);
