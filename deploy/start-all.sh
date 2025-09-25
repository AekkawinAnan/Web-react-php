#!/bin/bash
echo "🚀 Starting Bank Slip Reader (Frontend + Backend)"
echo "================================================="
echo ""
echo "📱 Frontend (React App): http://localhost:3000"
echo "🔧 Backend API: http://localhost:8000"
echo ""
echo "Starting both services..."
echo ""

# Function to cleanup on exit
cleanup() {
    echo ""
    echo "🛑 Shutting down services..."
    kill $frontend_pid $backend_pid 2>/dev/null
    exit
}

# Trap SIGINT (Ctrl+C)
trap cleanup SIGINT

# Start backend API
echo "📡 Starting Backend API..."
php -S localhost:8000 -t api/ > api.log 2>&1 &
backend_pid=$!

# Wait a moment for backend to start
sleep 2

# Start frontend
echo "🌐 Starting Frontend App..."
cd ..
serve -s dist -p 3000 > frontend.log 2>&1 &
frontend_pid=$!

echo ""
echo "✅ Both services started successfully!"
echo "📱 Frontend: http://localhost:3000"
echo "🔧 Backend:  http://localhost:8000"
echo ""
echo "Press Ctrl+C to stop both services"
echo ""

# Wait for processes
wait
