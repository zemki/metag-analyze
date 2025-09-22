# Firebase Configuration Guide

This guide explains how to set up Firebase for the Metag-Analyze project, which is required for push notifications to mobile devices.

## Prerequisites

- Firebase account (Google account required)
- Access to Firebase Console (https://console.firebase.google.com)
- Admin access to your Firebase project

## Step 1: Create or Access Firebase Project

1. **Go to Firebase Console**
   - Visit https://console.firebase.google.com
   - Sign in with your Google account

2. **Create New Project (if needed)**
   - Click "Add project"
   - Enter project name (e.g., "metag-analyze-prod" or "metag-analyze-dev")
   - Choose whether to enable Google Analytics
   - Click "Create project"

3. **Or Use Existing Project**
   - Select your existing Firebase project from the dashboard

## Step 2: Generate Service Account Key

1. **Navigate to Project Settings**
   - Click the gear icon (⚙️) next to "Project Overview"
   - Select "Project settings"

2. **Go to Service Accounts Tab**
   - Click on the "Service accounts" tab
   - Select "Firebase Admin SDK"

3. **Generate New Private Key**
   - Click "Generate new private key"
   - A JSON file will be downloaded
   - **IMPORTANT**: Keep this file secure and never commit it to version control

## Step 3: Configure Environment Variables

1. **Place the JSON File**
   Place the downloaded JSON file in your project root directory and rename it to something recognizable:
   ```bash
   # Example filename (use your actual project ID)
   metag-analyze-firebase-adminsdk.json
   ```

2. **Update .env File**
   Add these Firebase configuration variables to your `.env` file:

   ```env
   # Firebase Configuration
   FIREBASE_CREDENTIALS=./metag-analyze-firebase-adminsdk.json
   FIREBASE_URL=https://your-project-id-default-rtdb.firebaseio.com/
   ```

   **Where to find FIREBASE_URL:**
   - Go to Firebase Console → Your Project → Project Settings → General
   - The project ID is shown in the project information
   - Format: `https://your-project-id-default-rtdb.firebaseio.com/`
   - Note: This URL format is used even though we only use FCM

## Step 4: Configure Firebase Cloud Messaging (FCM)

1. **Enable FCM**
   - In Firebase Console, go to "Cloud Messaging"
   - FCM should be enabled by default for new projects

2. **Add Your App (Optional)**
   If you have a mobile app:
   - Click "Add app" and select your platform (iOS/Android)
   - Follow the platform-specific setup guide
   - Download the configuration files (google-services.json for Android, GoogleService-Info.plist for iOS)

## Step 5: Test Configuration

1. **Verify Environment Setup**
   Check that your environment variables are correctly set:
   ```bash
   php artisan tinker
   ```
   ```php
   // In tinker:
   echo config('services.firebase.credentials');
   echo config('services.firebase.database_url');
   ```

2. **Test FCM Connection**
   Create a simple test to verify FCM connectivity:
   ```bash
   php artisan tinker
   ```
   ```php
   // Test Firebase Admin SDK initialization for FCM
   $factory = (new \Kreait\Firebase\Factory)
       ->withServiceAccount(config('services.firebase.credentials'));

   $messaging = $factory->createMessaging();
   echo "Firebase FCM connection successful!";
   ```

## Security Best Practices

1. **Never Commit Credentials**
   - Add your Firebase JSON file to `.gitignore`
   - Example `.gitignore` entry:
   ```
   # Firebase credentials
   *firebase-adminsdk*.json
   metag-*-firebase-adminsdk*.json
   ```

2. **Use Environment-Specific Projects**
   - Development: `metag-analyze-dev`
   - Staging: `metag-analyze-staging`
   - Production: `metag-analyze-prod`

3. **Restrict Service Account Permissions**
   - In Firebase Console → IAM & Admin
   - Ensure service account has only necessary permissions
   - Recommended roles: "Firebase Admin SDK Service Agent"

## Troubleshooting

### "Credentials file not found"
- Verify the file path in `FIREBASE_CREDENTIALS` is correct
- Check file permissions (should be readable by web server)
- Ensure the file exists and is valid JSON

### "Invalid credentials"
- Re-download the service account key from Firebase Console
- Verify the JSON file is not corrupted
- Check that the project ID matches your Firebase project

### "Database URL invalid"
- Verify the URL format: `https://PROJECT_ID-default-rtdb.firebaseio.com/`
- Use the standard format even though we only use FCM
- Check that the URL ends with a trailing slash

### FCM Permission Denied
- Ensure your service account has "Firebase Cloud Messaging API Admin" role
- Verify FCM is enabled in Firebase Console
- Check IAM settings in Firebase Console

## Environment-Specific Configuration

### Development
```env
FIREBASE_CREDENTIALS=./metag-analyze-dev-firebase-adminsdk.json
FIREBASE_URL=https://metag-analyze-dev-default-rtdb.firebaseio.com/
```

### Production
```env
FIREBASE_CREDENTIALS=/path/to/secure/location/metag-analyze-prod-firebase-adminsdk.json
FIREBASE_URL=https://metag-analyze-prod-default-rtdb.firebaseio.com/
```

## Additional Resources

- [Firebase Admin SDK Documentation](https://firebase.google.com/docs/admin/setup)
- [Firebase Cloud Messaging Documentation](https://firebase.google.com/docs/cloud-messaging)
- [Laravel Firebase Package](https://firebase-php.readthedocs.io/)

## Next Steps

After completing Firebase FCM setup:
1. Test push notifications to mobile devices
2. Configure notification scheduling in your Laravel application
3. Set up Firebase Analytics (optional)
4. Configure FCM server key for mobile app integration