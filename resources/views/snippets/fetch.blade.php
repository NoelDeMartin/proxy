{{-- Generated with PrismJS — https://prismjs.com --}}
{{-- Source in resources/snippets/fetch.js --}}
<pre
    class="language-js"
    tabindex="0"
><code class=" language-js"><span class="token keyword">const</span> request <span class="token operator">=</span> <span class="token punctuation">{</span> url<span class="token operator">:</span> <span class="token string">'https://duckduckgo.com'</span> <span class="token punctuation">}</span><span class="token punctuation">;</span>
<span class="token keyword">const</span> response <span class="token operator">=</span> <span class="token keyword">await</span> <span class="token function">fetch</span><span class="token punctuation">(</span><span class="token string">'/fetch'</span><span class="token punctuation">,</span> <span class="token punctuation">{</span>
    method<span class="token operator">:</span> <span class="token string">'POST'</span><span class="token punctuation">,</span>
    headers<span class="token operator">:</span> <span class="token punctuation">{</span> <span class="token string">'Content-Type'</span><span class="token operator">:</span> <span class="token string">'application/json'</span> <span class="token punctuation">}</span><span class="token punctuation">,</span>
    body<span class="token operator">:</span> <span class="token constant">JSON</span><span class="token punctuation">.</span><span class="token function">stringify</span><span class="token punctuation">(</span>request<span class="token punctuation">)</span><span class="token punctuation">,</span>
<span class="token punctuation">}</span><span class="token punctuation">)</span><span class="token punctuation">;</span>
<span class="token keyword">const</span> html <span class="token operator">=</span> <span class="token keyword">await</span> response<span class="token punctuation">.</span><span class="token function">text</span><span class="token punctuation">(</span><span class="token punctuation">)</span><span class="token punctuation">;</span>

console<span class="token punctuation">.</span><span class="token function">log</span><span class="token punctuation">(</span><span class="token template-string"><span class="token template-punctuation string">`</span><span class="token string">Website HTML: </span><span class="token interpolation"><span class="token interpolation-punctuation punctuation">${</span>html<span class="token interpolation-punctuation punctuation">}</span></span><span class="token template-punctuation string">`</span></span><span class="token punctuation">)</span><span class="token punctuation">;</span></code></pre>
