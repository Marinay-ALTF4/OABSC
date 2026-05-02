import 'package:flutter/material.dart';

/// Responsive helper utilities for adaptive layouts
class ResponsiveHelper {
  static const double mobileBreakpoint = 600;
  static const double tabletBreakpoint = 900;

  /// Check if the screen is a small mobile device
  static bool isSmallMobile(BuildContext context) {
    return MediaQuery.of(context).size.width < 360;
  }

  /// Check if the screen is a mobile device
  static bool isMobile(BuildContext context) {
    return MediaQuery.of(context).size.width < mobileBreakpoint;
  }

  /// Check if the screen is a tablet device
  static bool isTablet(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    return width >= mobileBreakpoint && width < tabletBreakpoint;
  }

  /// Check if the screen is a desktop device
  static bool isDesktop(BuildContext context) {
    return MediaQuery.of(context).size.width >= tabletBreakpoint;
  }

  /// Get screen width
  static double screenWidth(BuildContext context) {
    return MediaQuery.of(context).size.width;
  }

  /// Get screen height
  static double screenHeight(BuildContext context) {
    return MediaQuery.of(context).size.height;
  }

  /// Get content padding based on screen size
  static EdgeInsets contentPadding(BuildContext context) {
    if (isSmallMobile(context)) {
      return const EdgeInsets.all(12);
    } else if (isMobile(context)) {
      return const EdgeInsets.all(16);
    } else if (isTablet(context)) {
      return const EdgeInsets.all(24);
    }
    return const EdgeInsets.all(32);
  }

  /// Get the number of stat card columns based on screen size
  static int statCardColumns(BuildContext context) {
    final width = screenWidth(context);
    if (width < 400) return 2;
    if (width < 600) return 2;
    if (width < 900) return 3;
    return 4;
  }

  /// Get card width for login/role selection screens
  static double authCardWidth(BuildContext context) {
    final width = screenWidth(context);
    if (width < 400) return width * 0.92;
    if (width < 600) return width * 0.88;
    return 420;
  }

  /// Responsive font scale factor
  static double fontScale(BuildContext context) {
    final width = screenWidth(context);
    if (width < 320) return 0.85;
    if (width < 360) return 0.9;
    if (width < 400) return 0.95;
    return 1.0;
  }
}
