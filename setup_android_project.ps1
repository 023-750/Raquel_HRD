# ============================================================
#  Raquel Pawnshop HRIS - Direct Launch Setup Script (No Splash Screen)
# ============================================================

$PROJECT_PATH = "D:\aok"
$PACKAGE_DIR   = "$PROJECT_PATH\app\src\main\java\com\example\raquelpawnshophris"
$RES           = "$PROJECT_PATH\app\src\main\res"
$MANIFEST      = "$PROJECT_PATH\app\src\main\AndroidManifest.xml"
$GRADLE_APP    = "$PROJECT_PATH\app\build.gradle.kts"

Write-Host "Updating project files at: $PROJECT_PATH..." -ForegroundColor Yellow

# ── 1. AndroidManifest.xml (MainActivity is now LAUNCHER) ───────────────────
@'
<?xml version="1.0" encoding="utf-8"?>
<manifest xmlns:android="http://schemas.android.com/apk/res/android">

    <uses-permission android:name="android.permission.INTERNET" />
    <uses-permission android:name="android.permission.ACCESS_NETWORK_STATE" />
    <uses-permission android:name="android.permission.CAMERA" />
    <uses-permission android:name="android.permission.READ_EXTERNAL_STORAGE" android:maxSdkVersion="32" />
    <uses-permission android:name="android.permission.READ_MEDIA_IMAGES" />

    <application
        android:allowBackup="true"
        android:icon="@mipmap/ic_launcher"
        android:roundIcon="@mipmap/ic_launcher_round"
        android:label="Raquel Pawnshop HRIS"
        android:supportsRtl="true"
        android:theme="@style/Theme.RaquelHRIS"
        android:usesCleartextTraffic="true"
        android:hardwareAccelerated="true">

        <!-- MainActivity is the DIRECT launcher activity (No splash screen) -->
        <activity
            android:name=".MainActivity"
            android:exported="true"
            android:screenOrientation="portrait"
            android:configChanges="orientation|screenSize|keyboardHidden"
            android:windowSoftInputMode="adjustResize">
            <intent-filter>
                <action android:name="android.intent.action.MAIN" />
                <category android:name="android.intent.category.LAUNCHER" />
            </intent-filter>
        </activity>

    </application>
</manifest>
'@ | Set-Content -Path $MANIFEST -Encoding UTF8
Write-Host "1/4 AndroidManifest.xml updated." -ForegroundColor Green

# Remove SplashActivity.java if present
if (Test-Path "$PACKAGE_DIR\SplashActivity.java") {
    Remove-Item "$PACKAGE_DIR\SplashActivity.java" -Force
}
if (Test-Path "$RES\layout\activity_splash.xml") {
    Remove-Item "$RES\layout\activity_splash.xml" -Force
}
Write-Host "2/4 SplashActivity files removed." -ForegroundColor Green

# ── 2. themes.xml ───────────────────────────────────────────────────────────
@'
<?xml version="1.0" encoding="utf-8"?>
<resources>
    <style name="Theme.RaquelHRIS" parent="Theme.MaterialComponents.DayNight.NoActionBar">
        <item name="colorPrimary">@color/primary_green</item>
        <item name="colorAccent">@color/primary_gold</item>
        <item name="android:statusBarColor">@color/primary_green</item>
        <item name="android:navigationBarColor">@color/primary_green</item>
    </style>
</resources>
'@ | Set-Content -Path "$RES\values\themes.xml" -Encoding UTF8
Write-Host "3/4 themes.xml updated." -ForegroundColor Green

Write-Host "4/4 All updates applied successfully!" -ForegroundColor Green
