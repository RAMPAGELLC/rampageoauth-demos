const express = require("express");
const http = (...args) => import('node-fetch').then(({default: http}) => fetch(...args));
const cookieSession = require('cookie-session')
const app = express();
const PORT = process.env.PORT || 8080;
const scopes = ["target_roblox", "roblox_username", "roblox_display", "roblox_id", "roblox_avatar_url"];

app.use(cookieSession({
    name: 'rampage_oauth_session',
    keys: ["securekey123"],
    maxAge: 24 * 60 * 60 * 1000 // 24 hours
}));

app.get("/", (req, res) => {
    if (!req.session.rampage_oauth_session.loggedin) return res.redirect(`http://localhost:${PORT}/login`);
    res.redirect(`http://localhost:${PORT}/dashboard`);
});

app.get("/dashboard", (req, res) => {
    if (!req.session.rampage_oauth_session.loggedin) return res.redirect(`http://localhost:${PORT}/login`);
    res.send(req.session.rampage_oauth_session.data)
});

app.get("/logout", (req, res) => {
    if (!req.session.rampage_oauth_session.loggedin) return res.redirect(`http://localhost:${PORT}/login`);
    req.session = null;
    res.send("Logged out!")
});

app.get("/authorize", (req, res) => {
    const target_roblox_key = req.query.target_roblox_key;

    if (!target_roblox_key) return console.log("No key found");

    const response = await fetch('https://id.rampage.place/oauth-api/redeem', {
        body: {
            key: target_roblox_key,
            platform: "roblox"
        }
    });
    const data = await response.json();

    if (!data.success) return console.log("Failed to verify success");
    req.session.rampage_oauth_session.loggedin = true
    req.session.rampage_oauth_session.data = data
    res.redirect(`http://localhost:${PORT}/dashboard`);
});

app.get("/login", (req, res) => {
    const scopesFormated = scopes.join(",");
    const returnURL = `localhost:${PORT}/authorize`;
    const redirect = `https://id.rampage.place/oauth?scopes=${encodeURIComponent(scopesFormated)}&return_url=${encodeURIComponent(returnURL)}`;
    res.redirect(redirect);
});

app.listen(PORT, () => {
    console.log(`Server is running on port ${PORT}.`);
});
