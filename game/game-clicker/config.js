// Integrated config for local Docker setup
const CONFIG = {
  CONTRACT_ADDRESS: '0xYourDeployedContractAddress',
  CONTRACT_ABI: [],
  BACKEND_API: 'http://localhost:8080/api', // PHP backend API
  TOKEN_DECIMALS: 18,
  CLAIM_RATE: 100,
  SHOP_ITEMS: [
    { id: 'boost_click', name: 'Click Boost (+1 extra per tap)', costTokens: 5, type: 'boost' },
    { id: 'auto_tap', name: 'Auto Tap (0.5 pts/sec)', costTokens: 12, type: 'boost' },
    { id: 'double_claim', name: 'Double Claim (x2)', costTokens: 25, type: 'boost' }
  ],
  MARKET_INITIAL_PRICE_ETH: 0.001
};