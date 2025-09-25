import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import './index.css'
import App from './App.tsx'
import ApiTest from './ApiTest.tsx'

// Simple routing based on URL path
const path = window.location.pathname;
const PageComponent = path === '/test-api' ? ApiTest : App;

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <PageComponent />
  </StrictMode>,
)
