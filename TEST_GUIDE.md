# 🧪 My Playground - Test Guide

## 🚀 Quick Start

### Method 1: Using the Test Script (Recommended)
```bash
./start_test.sh
```

### Method 2: Manual Setup
```bash
# 1. Run setup test
php test_setup.php

# 2. Start PHP server
php -S localhost:8080

# 3. Open in browser
open http://localhost:8080/test.html
```

## 📋 Test Environment Status

✅ **PHP 8.4.8** - Version OK  
✅ **SQLite Database** - Connected and ready  
✅ **Credentials System** - Loaded successfully  
✅ **All Required Extensions** - PDO, JSON, cURL, etc.  
✅ **File Permissions** - All directories writable  
✅ **API Endpoints** - All available  

## 🎯 Test Pages

### Main Test Page
- **URL**: http://localhost:8080/test.html
- **Features**: Interactive test dashboard with all modules

### Individual Module Tests

#### 📚 Learning Modules
- **Card Slideshow**: http://localhost:8080/modules/learning/card/slideshow.php
- **Vocabulary Manager**: http://localhost:8080/modules/learning/voca/voca.html
- **Word Rolls**: http://localhost:8080/modules/learning/inst/word_rolls.php

#### 🗂️ Management Modules
- **CRUD Demo**: http://localhost:8080/modules/management/crud/data_list.php
- **Health Tracking**: http://localhost:8080/modules/management/myhealth/health_list.php
- **User Auth**: http://localhost:8080/system/auth/login.php

#### 🛠️ Tools Modules
- **News Search**: http://localhost:8080/modules/tools/news/search_news_form.php
- **Box Breathing**: http://localhost:8080/modules/tools/box/boxbreathe.php
- **Family Tour**: http://localhost:8080/modules/tools/tour/familytour.html

## 🧪 API Testing

### Quick API Tests
Use the test buttons on http://localhost:8080/test.html or test directly:

#### Vocabulary API
```bash
curl http://localhost:8080/modules/learning/voca/fetch_vocabulary.php
```

#### News API
```bash
curl -X POST http://localhost:8080/modules/tools/news/search_news.php \
  -H "Content-Type: application/json" \
  -d '{"query":"test","country":"kr"}'
```

#### System Check
```bash
curl http://localhost:8080/system/admin/system_check.php
```

## 🔧 Troubleshooting

### Common Issues

#### 1. PHP Server Won't Start
```bash
# Check if port 8080 is in use
lsof -i :8080

# Kill process if needed
kill -9 <PID>

# Try different port
php -S localhost:8081
```

#### 2. Database Connection Issues
```bash
# Check database file
ls -la config/database.sqlite

# Check permissions
chmod 666 config/database.sqlite
```

#### 3. Credentials Issues
```bash
# Check credentials file
ls -la config/credentials/

# Copy sample if needed
cp config/credentials/sample.php config/credentials/development.php
```

#### 4. File Permission Issues
```bash
# Fix permissions
chmod -R 755 .
chmod -R 777 config/logs/
chmod -R 777 resources/uploads/
```

### Debug Mode

Enable debug mode by setting environment variable:
```bash
export APP_DEBUG=1
php -S localhost:8080
```

## 📊 Test Results

### Expected Test Results
- ✅ All PHP extensions loaded
- ✅ Database tables created
- ✅ API endpoints responding
- ✅ File permissions correct
- ✅ Credentials loaded

### Performance Benchmarks
- **Page Load Time**: < 2 seconds
- **API Response Time**: < 1 second
- **Database Query Time**: < 100ms

## 🎮 Interactive Testing

### Test Dashboard Features
1. **Module Navigation** - Direct links to all modules
2. **API Testing** - One-click API tests
3. **Setup Verification** - Environment check
4. **Real-time Results** - Live test feedback

### Manual Testing Checklist
- [ ] All module pages load
- [ ] Database operations work
- [ ] API endpoints respond
- [ ] File uploads work
- [ ] User authentication works
- [ ] Responsive design works
- [ ] Dark mode toggle works

## 📝 Test Logs

Test logs are stored in:
- **PHP Error Log**: Check terminal output
- **Application Logs**: `config/logs/`
- **Database Logs**: SQLite database

## 🚀 Production Testing

For production testing:
1. Update credentials in `config/credentials/production.php`
2. Set up MySQL database
3. Configure web server (Apache/Nginx)
4. Set proper file permissions
5. Enable SSL certificate

---

**Happy Testing! 🎯**

For issues or questions, check the main [README.md](README.md) or create an issue on GitHub. 