import 'package:flutter/material.dart';
import 'package:loyalty_app/Services/language_service.dart';

class CustomBottomNav extends StatelessWidget {
  final int currentIndex;
  final Function(int) onTap;

  const CustomBottomNav({
    super.key, 
    required this.currentIndex, 
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final localizations = AppLocalizations.of(context)!;
    
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            spreadRadius: 0,
            blurRadius: 10,
            offset: const Offset(0, -5),
          ),
        ],
      ),
      child: SafeArea(
        top: false,
        bottom: true,
        minimum: const EdgeInsets.only(bottom: 8),
        child: SizedBox(
          height: 68, // Fixed height to prevent overflow
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 8),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildNavItem(
                  context,
                  index: 0,
                  icon: Icons.home_outlined,
                  activeIcon: Icons.home,
                  label: localizations.home,
                ),
                _buildNavItem(
                  context,
                  index: 1,
                  icon: Icons.card_giftcard_outlined,
                  activeIcon: Icons.card_giftcard,
                  label: localizations.rewards,
                ),
                _buildNavItem(
                  context,
                  index: 2,
                  icon: Icons.qr_code_scanner_outlined,
                  activeIcon: Icons.qr_code_scanner,
                  label: localizations.scan,
                  isCenter: true,
                ),
                _buildNavItem(
                  context,
                  index: 3,
                  icon: Icons.notifications_none_outlined,
                  activeIcon: Icons.notifications,
                  label: localizations.alerts,
                ),
                _buildNavItem(
                  context,
                  index: 4,
                  icon: Icons.person_outline,
                  activeIcon: Icons.person,
                  label: localizations.account,
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildNavItem(
    BuildContext context, {
    required int index,
    required IconData icon,
    required IconData activeIcon,
    required String label,
    bool isCenter = false,
  }) {
    final isSelected = currentIndex == index;
    
    return GestureDetector(
      onTap: () => onTap(index),
      behavior: HitTestBehavior.opaque,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        curve: Curves.easeInOut,
        padding: EdgeInsets.symmetric(
          horizontal: isCenter ? 16 : 12, 
          vertical: 6, // Reduced vertical padding
        ),
        constraints: BoxConstraints(
          minWidth: isCenter ? 60 : 48, // Minimum width constraints
        ),
        decoration: BoxDecoration(
          color: isSelected 
            ? (isCenter ? Colors.black : Colors.black.withOpacity(0.1))
            : Colors.transparent,
          borderRadius: BorderRadius.circular(isCenter ? 25 : 12),
          boxShadow: isSelected && isCenter
            ? [
                BoxShadow(
                  color: Colors.black.withOpacity(0.2),
                  spreadRadius: 0,
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ]
            : null,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            AnimatedSwitcher(
              duration: const Duration(milliseconds: 200),
              child: Icon(
                isSelected ? activeIcon : icon,
                key: ValueKey(isSelected),
                color: isSelected 
                  ? (isCenter ? Colors.white : Colors.black)
                  : Colors.grey.shade600,
                size: isCenter ? 26 : 22, // Slightly reduced icon sizes
              ),
            ),
            const SizedBox(height: 2), // Reduced spacing
            AnimatedDefaultTextStyle(
              duration: const Duration(milliseconds: 200),
              style: TextStyle(
                fontSize: isCenter ? 11 : 10, // Slightly smaller font
                fontWeight: isSelected ? FontWeight.w600 : FontWeight.w500,
                color: isSelected 
                  ? (isCenter ? Colors.white : Colors.black)
                  : Colors.grey.shade600,
              ),
              child: Text(label),
            ),
          ],
        ),
      ),
    );
  }
}